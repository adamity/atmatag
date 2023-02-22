@extends('layouts.app')

@section('content')
    <div class="container vh-100 text-white">
        <div class="d-flex flex-column justify-content-center align-items-center h-100 text-center">
            <img src="{{ asset('images/atmatag_full_2.png') }}" alt="atmatag logo" class="img-fluid height-150 border border-3 rounded-circle mb-4">
            <p class="fw-bold fs-1">Tag Disabled!</p>
            <p class="lead fs-4 mb-5">Sorry, this tag has been temorarily disabled.</p>
            <p class="lead fs-4 mb-5">Try AtmaTag now!<br>No registration, just click the button below.</p>

            <p class="d-inline-block bg-danger rounded-pill text-white user-select-none small fw-semibold px-3 py-1 mb-5" data-bs-toggle="tooltip" data-bs-title="This is a demo site, the data will be deleted without notice.">Demo Only ⓘ</p>
            <a href="https://t.me/AtmaTagBot" target="_blank" class="btn btn-sm btn-outline-light rounded-pill mx-auto">
                <div class="d-flex align-items-center">
                    <i class="fs-4 bi bi-telegram"></i>
                    <span class="ms-2">Get Started</span>
                </div>
            </a>
        </div>
    </div>
@endsection

@section('js')
    <script>
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
    </script>
@endsection
