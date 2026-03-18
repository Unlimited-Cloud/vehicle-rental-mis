<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}</title>
    <link href="{{ asset('adminlte/css/bootstrap.min.css') }}" rel="stylesheet" />
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <style>
        body {
            background: #f0f2f5;
            font-family: Arial, sans-serif;
        }

        /* Login card */
        .card-authentication1 {
            max-width: 400px;
            margin: 50px auto;
        }

        /* OTP Modal overlay - hidden by default */
        #otpModal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        #otpModal.active {
            display: flex;
        }

        /* OTP card style */
        #otpModal .otp-card {
            background: #1a3b8e;
            padding: 30px 20px;
            border-radius: 12px;
            width: 90%;
            max-width: 400px;
            text-align: center;
            color: white;
        }

        #otpModal h6 {
            font-weight: normal;
            margin-bottom: 10px;
        }

        #otpModal small {
            color: #cfd8dc;
            display: block;
            margin-bottom: 20px;
        }

        /* OTP inputs */
        .otp-input {
            width: 45px;
            height: 50px;
            margin: 0 5px;
            text-align: center;
            font-size: 1.5rem;
            border-radius: 8px;
            border: 2px solid #cfd8dc;
            background: transparent;
            color: white;
        }

        .otp-input:focus {
            border-color: #ff3b30;
            outline: none;
            box-shadow: 0 0 5px #ff3b30;
        }

        /* Buttons */
        .btn-validate {
            background-color: #ff3b30;
            color: white;
            font-size: 1.2rem;
            border-radius: 8px;
            width: 100%;
            margin-top: 20px;
            padding: 12px;
            border: none;
        }

        .btn-resend {
            background-color: #607d8b;
            color: white;
            font-size: 1rem;
            border-radius: 8px;
            width: 100%;
            margin-top: 10px;
            padding: 10px;
            border: none;
        }

        #otpError {
            color: #ffcccb;
            margin-top: 10px;
        }
    </style>
</head>

<body>

    <div class="card card-authentication1">
        <div class="card-body text-center">
            <img src="{{ asset('adminlte/logo2.png') }}" style="width:150px; margin-bottom:20px;">

            @if ($errors->any())
            <div class="alert alert-danger">
                @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
                @endforeach
            </div>
            @endif

            <form method="post" action="{{ route('login') }}">
                @csrf

                <div class="form-group">
                    <div class="position-relative has-icon-right">
                        <input type="text" class="form-control form-control-rounded" placeholder="E-Mail Address" name="email">
                        <span style="position:absolute; top:50%; right:15px; transform:translateY(-50%); cursor:pointer; color:#6c757d;">
                            <i class="fa fa-envelope"></i>
                        </span>
                    </div>
                </div>

                <div class="form-group">
                    <div class="position-relative">
                        <input type="password" id="password" name="password" class="form-control form-control-rounded pr-5" placeholder="Password">
                        <span id="togglePassword" style="position:absolute; top:50%; right:15px; transform:translateY(-50%); cursor:pointer; color:#6c757d;">
                            <i class="fa fa-eye" id="eyeIcon"></i>
                        </span>
                    </div>
                </div>

                <div class="form-row mr-0 ml-0">
                    <div class="form-group col-6">
                        <div class="icheck-primary">
                            <input type="checkbox" id="remember" name="remember" checked>
                            <label for="remember">Remember me</label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary shadow-primary btn-round btn-block">
                    Send Passcode
                </button>
            </form>
        </div>
    </div>

    {{-- OTP Modal --}}
    @if(session('otp_email') || $errors->has('otp') || session('success'))
    <div id="otpModal" class="active">
        <div class="otp-card">
            <button id="closeOtpModal" class="close-btn">&times;</button>
            <h6>Please enter the Passcode to verify your account</h6>
            <small>Passcode has been sent to <br>{{ session('otp_email') }}</small>

            <div id="otpError">
                @if($errors->has('otp')) {{ $errors->first('otp') }} @endif
                @if(session('success')) {{ session('success') }} @endif
            </div>

            <form id="otpForm" method="POST" action="{{ route('otp.verify') }}" class="d-flex justify-content-center mt-3">
                @csrf
                @for($i=0; $i<6; $i++)
                    <input type="text" class="otp-input" maxlength="1">
                    @endfor
                    <input type="hidden" name="otp" id="otpHidden">
            </form>

            <button class="btn-validate" id="validateBtn">Validate</button>

            <form method="POST" action="{{ route('otp.send') }}">
                @csrf
                <button type="submit" class="btn-resend">Resend OTP</button>
            </form>
        </div>
    </div>
    @endif

    <script src="{{ asset('adminlte/js/jquery.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Show OTP modal only if active
            if ($('#otpModal').hasClass('active')) {
                $('#otpModal').show();
            }

            // OTP input auto-focus & backspace
            $('.otp-input').on('input', function() {
                this.value = this.value.replace(/\D/g, '');
                if (this.value && $(this).next('.otp-input').length) $(this).next('.otp-input').focus();
            });

            $('.otp-input').on('keydown', function(e) {
                if (e.key === 'Backspace' && !this.value) $(this).prev('.otp-input').focus();
            });

            $('.otp-input').on('paste', function(e) {
                e.preventDefault();
                let paste = e.originalEvent.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6);
                $('.otp-input').each(function(i) {
                    this.value = paste[i] || '';
                });
            });

            // Combine 6 inputs into hidden input before submitting OTP form
            $('#validateBtn').on('click', function(e) {
                e.preventDefault();
                let otp = '';
                $('.otp-input').each(function() {
                    otp += this.value;
                });
                $('#otpHidden').val(otp);
                $('#otpForm').submit();
            });
        });
    </script>

    <script>
        $('#togglePassword').on('click', function() {
            const input = $('#password');
            const icon = $('#eyeIcon');

            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                input.attr('type', 'password');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });
    </script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('otpModal');
    const closeBtn = document.getElementById('closeOtpModal');

    // Close when clicking the button
    closeBtn.addEventListener('click', function() {
        modal.classList.remove('active');
    });

    // Optional: close when clicking outside the card
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.classList.remove('active');
        }
    });
});
</script>
</body>
<style>
#otpModal.active {
    display: flex;
    justify-content: center;
    align-items: center;
    position: fixed;
    inset: 0;
    background-color: rgba(0,0,0,0.5);
    z-index: 1000;
}

.otp-card {
    position: relative;
    background: #fff;
    padding: 2rem;
    border-radius: 8px;
    text-align: center;
    max-width: 400px;
    width: 90%;
}

.close-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    border: none;
    background: none;
    font-size: 1.5rem;
    cursor: pointer;
}
</style>

</html>