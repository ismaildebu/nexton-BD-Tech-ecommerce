@extends('layouts.admin')
@section('title', __('Dashboard'))

@section('content')
    <!-- 4 Top Metric Cards (Image 1 Style) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <!-- Card 1: Total Sales -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div class="w-12 h-12 rounded-xl bg-purple-50 text-[#6852D9] flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-600">
                    +15.8%
                </span>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-extrabold text-slate-800 tracking-tight">৳ {{ number_format($revenue, 2) }}</div>
                <div class="text-xs font-medium text-slate-400 mt-1">{{ __('Total Sales & Revenue') }}</div>
            </div>
        </div>

        <!-- Card 2: Orders Placed -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div class="w-12 h-12 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-600">
                    {{ $orders }} active
                </span>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-extrabold text-slate-800 tracking-tight">{{ number_format($orders) }}</div>
                <div class="text-xs font-medium text-slate-400 mt-1">{{ __('Orders Placed') }}</div>
            </div>
        </div>

        <!-- Card 3: Active Customers -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div class="w-12 h-12 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-600">
                    +8.2%
                </span>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-extrabold text-slate-800 tracking-tight">{{ number_format($customers) }}</div>
                <div class="text-xs font-medium text-slate-400 mt-1">{{ __('Registered Customers') }}</div>
            </div>
        </div>

        <!-- Card 4: Vendors / Pending -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $pendingVendors > 0 ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600' }}">
                    {{ $pendingVendors }} pending
                </span>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-extrabold text-slate-800 tracking-tight">{{ number_format($vendors) }}</div>
                <div class="text-xs font-medium text-slate-400 mt-1">{{ __('Marketplace Vendors') }}</div>
            </div>
        </div>
    </div>

    <!-- Charts Row (Image 1 & 3 Style) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Donut Chart: Sales Distribution -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-1">
                    <h3 class="font-bold text-slate-800 text-sm">{{ __('Total Sales Distribution') }}</h3>
                    <span class="text-xs text-slate-400">All Time</span>
                </div>
                <p class="text-xs text-slate-400 mb-4">{{ __('Channel breakdown across all store operations.') }}</p>
                <div class="relative flex items-center justify-center my-4">
                    <div class="w-48 h-48">
                        <canvas id="donutChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-2 pt-4 border-t border-slate-100 text-center text-xs">
                <div>
                    <div class="flex items-center justify-center gap-1.5 text-slate-500 font-medium">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#6852D9]"></span> Online
                    </div>
                    <div class="font-bold text-slate-800 mt-1">62%</div>
                </div>
                <div>
                    <div class="flex items-center justify-center gap-1.5 text-slate-500 font-medium">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#20B59A]"></span> Vendor
                    </div>
                    <div class="font-bold text-slate-800 mt-1">28%</div>
                </div>
                <div>
                    <div class="flex items-center justify-center gap-1.5 text-slate-500 font-medium">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#F2B75D]"></span> B2B
                    </div>
                    <div class="font-bold text-slate-800 mt-1">10%</div>
                </div>
            </div>
        </div>

        <!-- Combo Bar + Line Chart: Sales Analytics -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-slate-800 text-sm">{{ __('Sales Analytics & Forecast') }}</h3>
                    <p class="text-xs text-slate-400 mt-0.5">{{ __('Comparison of actual sales vs projected performance.') }}</p>
                </div>
                <div class="flex items-center gap-4 text-xs">
                    <div class="flex items-center gap-1.5 text-slate-500">
                        <span class="w-2.5 h-2.5 rounded-sm bg-[#6852D9]"></span> Actual Sales
                    </div>
                    <div class="flex items-center gap-1.5 text-slate-500">
                        <span class="w-2.5 h-0.5 bg-emerald-500"></span> Projected
                    </div>
                    <a href="{{ route('admin.orders.index') }}" class="font-semibold text-[#6852D9] hover:underline flex items-center gap-1">
                        View Reports &rarr;
                    </a>
                </div>
            </div>
            <div class="h-64">
                <canvas id="salesAnalyticsChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Bottom Data Tables Row (Image 1 & 3 Style) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Product Inventory Table -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col justify-between">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-800 text-sm">{{ __('Product Inventory') }}</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">{{ __('Stock status and pricing breakdown') }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.products.index') }}" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-[#6852D9] text-white hover:bg-[#5741c4] transition flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Add Product</span>
                    </a>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50/70 text-slate-400 font-semibold uppercase text-[10px] tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-4">Product</th>
                            <th class="py-3 px-3">Stock</th>
                            <th class="py-3 px-3">Price</th>
                            <th class="py-3 px-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($inventoryProducts as $prod)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3 px-4 flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200/60 flex items-center justify-center shrink-0 text-[#6852D9] font-bold">
                                        <svg class="w-5 h-5 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                    </div>
                                    <div class="truncate max-w-[160px]">
                                        <div class="font-semibold text-slate-800 truncate">{{ $prod->name }}</div>
                                        <div class="text-[10px] text-slate-400">{{ $prod->category->name ?? 'General' }}</div>
                                    </div>
                                </td>
                                <td class="py-3 px-3 font-medium text-slate-600 whitespace-nowrap">
                                    {{ $prod->inventory->quantity ?? '15' }} units
                                </td>
                                <td class="py-3 px-3 font-semibold text-slate-800 whitespace-nowrap">
                                    ৳ {{ number_format($prod->price, 2) }}
                                </td>
                                <td class="py-3 px-3 whitespace-nowrap">
                                    @php $qty = $prod->inventory->quantity ?? 15; @endphp
                                    @if ($qty > 5)
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                        </span>
                                    @elseif ($qty > 0)
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Low Stock
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-50 text-rose-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Out of Stock
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-slate-400 text-xs">No products found in inventory.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3 border-t border-slate-100 text-center">
                <a href="{{ route('admin.products.index') }}" class="text-xs font-semibold text-[#6852D9] hover:underline">
                    View all {{ $products }} products &rarr;
                </a>
            </div>
        </div>

        <!-- Recent Orders Table -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col justify-between">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-800 text-sm">{{ __('Recent Orders') }}</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">{{ __('Latest customer purchases and transactions') }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.orders.index') }}" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
                        Export CSV
                    </a>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50/70 text-slate-400 font-semibold uppercase text-[10px] tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-4">Customer</th>
                            <th class="py-3 px-3">Order ID</th>
                            <th class="py-3 px-3">Amount</th>
                            <th class="py-3 px-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($recentOrders as $order)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3 px-4 flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-slate-200 to-slate-300 text-slate-700 font-bold flex items-center justify-center shrink-0 text-xs">
                                        {{ substr($order->user->name ?? 'Customer', 0, 1) }}
                                    </div>
                                    <div class="truncate max-w-[120px]">
                                        <div class="font-semibold text-slate-800 truncate">{{ $order->user->name ?? 'Customer' }}</div>
                                        <div class="text-[10px] text-slate-400">{{ $order->created_at->format('d M Y') }}</div>
                                    </div>
                                </td>
                                <td class="py-3 px-3 font-mono font-medium text-slate-600 whitespace-nowrap">
                                    {{ $order->order_number }}
                                </td>
                                <td class="py-3 px-3 font-semibold text-slate-800 whitespace-nowrap">
                                    ৳ {{ number_format($order->total, 2) }}
                                </td>
                                <td class="py-3 px-3 whitespace-nowrap">
                                    @if ($order->status === 'completed' || $order->status === 'delivered')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700">
                                            Completed
                                        </span>
                                    @elseif ($order->status === 'pending')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700">
                                            In Progress
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-slate-400 text-xs">No orders recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3 border-t border-slate-100 text-center">
                <a href="{{ route('admin.orders.index') }}" class="text-xs font-semibold text-[#6852D9] hover:underline">
                    View all {{ $orders }} orders &rarr;
                </a>
            </div>
        </div>
    </div>

    <!-- Chart Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Donut Chart
            const donutCtx = document.getElementById('donutChart');
            if (donutCtx) {
                new Chart(donutCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Online Store', 'Vendor Marketplace', 'B2B Revenue'],
                        datasets: [{
                            data: [62, 28, 10],
                            backgroundColor: ['#6852D9', '#20B59A', '#F2B75D'],
                            borderWidth: 0,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '72%',
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return ' ' + context.label + ': ' + context.raw + '%';
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Combo Bar + Line Chart
            const analyticsCtx = document.getElementById('salesAnalyticsChart');
            if (analyticsCtx) {
                new Chart(analyticsCtx, {
                    data: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                        datasets: [
                            {
                                type: 'bar',
                                label: 'Online Sales',
                                data: [1200, 1050, 1300, 1100, 1450, 1600, 1350, 1400, 1550, 1680, 1750, 1900],
                                backgroundColor: '#6852D9',
                                borderRadius: 6,
                                barThickness: 14
                            },
                            {
                                type: 'bar',
                                label: 'Vendor Sales',
                                data: [800, 650, 900, 750, 950, 1100, 850, 920, 1050, 1150, 1200, 1350],
                                backgroundColor: '#E2E8F0',
                                borderRadius: 6,
                                barThickness: 14
                            },
                            {
                                type: 'line',
                                label: 'Projected Sales',
                                data: [2100, 2200, 2150, 2250, 2400, 2550, 2300, 2450, 2500, 2700, 2850, 3100],
                                borderColor: '#10B981',
                                borderDash: [5, 5],
                                borderWidth: 2,
                                pointBackgroundColor: '#10B981',
                                pointRadius: 4,
                                pointHoverRadius: 6,
                                fill: false,
                                tension: 0.3
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false
                        },
                        scales: {
                            x: {
                                grid: { display: false }
                            },
                            y: {
                                grid: { color: '#F1F5F9' },
                                ticks: {
                                    callback: function(value) { return '৳' + value; }
                                }
                            }
                        },
                        plugins: {
                            legend: { display: false }
                        }
                    }
                });
            }
        });
    </script>
@endsection
