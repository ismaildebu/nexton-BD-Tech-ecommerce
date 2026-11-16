@extends('layouts.admin')
@section('content')
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow p-5 border-l-4 border-[#6852D9]">
            <div class="text-[#8993A4] text-sm">{{ __('Customers') }}</div>
            <div class="text-[24px] font-bold mt-1 text-[#343A46]">{{ $customers }}</div>
        </div>
        <div class="bg-white rounded-xl shadow p-5 border-l-4 border-[#20B59A]">
            <div class="text-[#8993A4] text-sm">{{ __('Vendors') }}</div>
            <div class="text-[24px] font-bold mt-1 text-[#343A46]">{{ $vendors }}</div>
        </div>
        <div class="bg-white rounded-xl shadow p-5 border-l-4 border-[#F2B75D]">
            <div class="text-[#8993A4] text-sm">{{ __('Pending vendors') }}</div>
            <div class="text-[24px] font-bold mt-1 text-[#343A46]">{{ $pendingVendors }}</div>
        </div>
        <div class="bg-white rounded-xl shadow p-5 border-l-4 border-[#4B83D6]">
            <div class="text-[#8993A4] text-sm">{{ __('Total sales') }}</div>
            <div class="text-[24px] font-bold mt-1 text-[#343A46]">৳ 0.00</div>
            <div class="text-xs text-[#8993A4] mt-1">{{ __('Order module not available') }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow p-5">
            <h3 class="text-[16px] font-semibold mb-3 text-[#343A46]">{{ __('Sales distribution') }}</h3>
            <canvas id="donutChart" height="220"></canvas>
        </div>
        <div class="bg-white rounded-xl shadow p-5">
            <h3 class="text-[16px] font-semibold mb-3 text-[#343A46]">{{ __('Monthly sales') }}</h3>
            <canvas id="barChart" height="220"></canvas>
        </div>
    </div>

    <script>
        new Chart(document.getElementById('donutChart'), {
            type: 'doughnut',
            data: { labels: ['{{ __("Online") }}', '{{ __("Marketplace") }}'], datasets: [{ data: [0, 0], backgroundColor: ['#6852D9', '#20B59A'] }] },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });
        new Chart(document.getElementById('barChart'), {
            type: 'bar',
            data: { labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'], datasets: [{ label: '{{ __("Sales") }}', data: Array(12).fill(0), backgroundColor: '#4B83D6' }] },
            options: { responsive: true }
        });
    </script>
@endsection