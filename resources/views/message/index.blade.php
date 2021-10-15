@extends('layouts.app')

@section('alert')
<div id="alert-message" class="alert alert-success alert-fixed text-center fixed-top d-none">
    <p id="successMessage" class="m-0 muli-semi-bold tundora"></p>
</div>

<div id="alert-error" class="alert alert-danger alert-fixed text-center fixed-top d-none">
    <p id="errorMessage" class="m-0 muli-extra-bold text-danger"></p>
</div>
@endsection

@section('content')
<div class="container" style="height: 100vh">
    <div class="row py-5 h-100 align-items-center">
        <div class="col-12 col-md-6 offset-md-3 h-100">
            <div class="container-fluid border rounded h-100">
                <div class="d-flex flex-column justify-content-between h-100">
                    <p class="text-center mt-5 h4">{{ $message }}</p>

                    <div>
                        @if ($contact_number)
                            <a href="tel:{{ $contact_number }}" class="btn btn-primary mt-4 w-100">Call Me</a>
                        @endif
        
                        <form id="messageForm" class="my-4">
                            <div class="form-group">
                                <textarea placeholder="Write a message" class="form-control" id="message" rows="3" required></textarea>
                            </div>
        
                            <button id="messageSubmit" type="submit" class="btn btn-primary mt-4 w-100">Send</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
@include('message.main-js')
@endsection
