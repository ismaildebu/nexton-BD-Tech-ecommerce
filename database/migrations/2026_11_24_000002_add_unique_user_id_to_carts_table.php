<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            $duplicateUserIds = DB::table('carts')
                ->select('user_id')
                ->whereNotNull('user_id')
                ->groupBy('user_id')
                ->havingRaw('COUNT(*) > 1')
                ->orderBy('user_id')
                ->pluck('user_id');

            foreach ($duplicateUserIds as $userId) {
                $carts = DB::table('carts')
                    ->where('user_id', $userId)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                // The oldest cart is the stable canonical cart for this owner.
                $canonicalCartId = $carts->first()->id;

                foreach ($carts->skip(1) as $duplicateCart) {
                    $items = DB::table('cart_items')
                        ->where('cart_id', $duplicateCart->id)
                        ->orderBy('product_id')
                        ->orderBy('id')
                        ->lockForUpdate()
                        ->get();

                    foreach ($items as $item) {
                        $canonicalItem = DB::table('cart_items')
                            ->where('cart_id', $canonicalCartId)
                            ->where('product_id', $item->product_id)
                            ->lockForUpdate()
                            ->first();

                        if ($canonicalItem !== null) {
                            DB::table('cart_items')
                                ->where('id', $canonicalItem->id)
                                ->increment('quantity', (int) $item->quantity);

                            DB::table('cart_items')->where('id', $item->id)->delete();
                        } else {
                            DB::table('cart_items')
                                ->where('id', $item->id)
                                ->update(['cart_id' => $canonicalCartId]);
                        }
                    }

                    DB::table('carts')->where('id', $duplicateCart->id)->delete();
                }

                // A merged authenticated cart must not remain attached to a guest session.
                DB::table('carts')->where('id', $canonicalCartId)->update(['session_id' => null]);
            }
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->unique('user_id', 'carts_user_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropUnique('carts_user_id_unique');
        });
    }
};
