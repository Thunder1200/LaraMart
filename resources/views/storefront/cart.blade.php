@extends('layouts.app')

@section('title', 'Sepetim - LaraMart')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-extrabold text-slate-900">Alışveriş Sepetim</h1>
    <livewire:cart-manager />
</div>
@endsection

