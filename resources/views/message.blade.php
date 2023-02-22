@extends('layouts.app')

@section('content')
    <div id="alert-message" class="alert alert-success alert-fixed text-center fixed-top d-none">
        <p id="successMessage" class="m-0 muli-semi-bold tundora"></p>
    </div>

    <div id="alert-error" class="alert alert-danger alert-fixed text-center fixed-top d-none">
        <p id="errorMessage" class="m-0 muli-extra-bold text-danger"></p>
    </div>

    <div class="container vh-100 text-white">
        <div class="d-flex flex-column justify-content-center align-items-center h-100 text-center">
            <form id="messageForm">
                <div class="card bg-glass">
                    <div class="card-header">
                        <p class="fw-bold fs-4">{{ $message }}</p>
                    </div>

                    <div class="card-body">
                        <textarea placeholder="Write a message" class="form-control" name="message" id="message" cols="30" rows="10" required></textarea>
                    </div>

                    <div class="card-footer">
                        <div class="d-flex justify-content-between">
                            <a href="tel:{{ $contact_number }}" class="btn btn-outline-light rounded-pill @if(!$contact_number) invisible @endif">Call Me</a>
                            <button id="messageSubmit" type="submit" class="btn btn-light rounded-pill">Send Message</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#alert-message').hide().removeClass("d-none");
        $('#alert-error').hide().removeClass("d-none");
        $('#message').val('');

        let messageForm = $("#messageForm");
        messageForm.validate();

        $("#messageSubmit").click(function(e) {
            e.preventDefault();
            $("#messageSubmit").addClass("disabled");
            check = messageForm.valid();

            if (check) {
                let message = $('#message').val();
                let contact_id = '{{ $contact_id }}';

                $.ajax({
                    type: 'POST',
                    url: "{{ route('send') }}",
                    data:{
                        _token: '{{ csrf_token() }}',
                        message: message,
                        contact_id: contact_id,
                    },
                    success:function (data) {
                        if (data.success) {
                            $('#message').val('');
                            $('#successMessage').text("Message Successfully Sent!");
                            $('#alert-message').fadeIn();
                            setTimeout(function(){ $('#alert-message').fadeOut(); }, 5000);
                        } else {
                            $('#errorMessage').text("Message Unsuccessfully Sent!");
                            $('#alert-error').fadeIn();
                            setTimeout(function(){ $('#alert-error').fadeOut(); }, 5000);
                        }

                        $("#messageSubmit").removeClass("disabled");
                    }
                });
            } else {
                $("#messageSubmit").removeClass("disabled");
            }
        });
    </script>
@endsection
