<script>
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
