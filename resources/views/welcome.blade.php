@extends('layouts.app')

@section('content')
    <div class="container vh-100 text-white">
        <div class="d-flex flex-column justify-content-center align-items-center h-100 text-center">
            <img src="{{ asset('images/atmatag_full_2.png') }}" alt="atmatag logo" class="img-fluid height-150 border border-3 rounded-circle mb-4">
            <p class="fw-bold fs-1">AtmaTag</p>
            <p class="lead fs-4 mb-5">Gets Lost Items Back to You</p>

            <a href="https://t.me/AtmaTagBot" target="_blank" class="btn btn-sm btn-outline-light rounded-pill mx-auto">
                <div class="d-flex align-items-center">
                    <i class="fs-4 bi bi-telegram"></i>
                    <span class="ms-2">Get Started</span>
                </div>
            </a>
        </div>
    </div>
@endsection