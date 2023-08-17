@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1 class="text-black-50">You are logged in!</h1>
        <canvas id="signature-pad" width="400" height="200"></canvas>
        <button id="save-signature">Save Signature</button>
        <button id="clear-signature">Clear Signature</button>
        <img id="signature-image" src="" alt="Signature Image">
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            var canvas = document.getElementById('signature-pad');
            var signaturePad = new SignaturePad(canvas);

            // Optional: Customize the appearance of the signature pad
            signaturePad.penColor = 'black'; // Change the pen color
            signaturePad.backgroundColor = 'rgba(0, 0, 0, 0)'; // Set the background color

            /// Handle save button click event
            $('#save-signature').on('click', function() {
                if (signaturePad.isEmpty()) {
                    alert('Please provide your signature.');
                } else {
                    // Get the signature data as a base64-encoded PNG image
                    var signatureData = signaturePad.toDataURL();

                    // Set the signature image source
                    $('#signature-image').attr('src', signatureData);

                    // Send the signature data to the Laravel route via AJAX
                    $.ajax({
                        type: 'POST',
                        url: '/save-signature', // Replace this with your Laravel route URL
                        data: {
                            image_data: signatureData
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            // The server-side script can send back a response if needed
                            console.log(response);
                        },
                        error: function() {
                            alert('Failed to save the signature.');
                        }
                    });
                }
            });

            // Handle clear button click event
            $('#clear-signature').on('click', function() {
                signaturePad.clear();
                canvas.style.backgroundColor = 'rgba(0, 0, 0, 0)';
                $('#signature-image').attr('src', '');
            });
        });
    </script>
@endsection
