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
        .otp-modal {
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

        .otp-modal.active {
            display: flex;
        }

        /* OTP card style */
        .otp-card {
            background: #1a3b8e;
            padding: 30px 20px;
            border-radius: 12px;
            width: 90%;
            max-width: 400px;
            text-align: center;
            color: white;
            position: relative;
        }

        .otp-card h6 {
            font-weight: normal;
            margin-bottom: 10px;
        }

        .otp-card small {
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

        .close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            border: none;
            background: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: white;
        }

        .close-btn:hover {
            color: #ff3b30;
        }

        #otpError {
            color: #ffcccb;
            margin-top: 10px;
        }

        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>

<body>

    <div class="card card-authentication1">

        <div class="card-body text-center">

            
            @php
               use App\Helpers\MenuHelper;
               $basic = MenuHelper::showBasicSetup();
            @endphp

            @if($basic && $basic->login_logo)
               <img src="{{ asset($basic->login_logo) }}" class="img-fluid rounded" width="100" alt="Company Logo">
            @else
               <img src="{{ asset('adminlte/logo3.png') }}" style="width:150px; margin-bottom:20px;"> 
            @endif


            @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
     @endif

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
                        <input type="text" class="form-control form-control-rounded" placeholder="E-Mail Address" name="email" value="{{ old('email') }}">
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

                <div class="text-right mb-3">
                    <a href="#" id="forgotPasswordLink">Forgot Password?</a>
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
                    Login
                </button>
            </form>
        </div>
    </div>

    <!-- FORGOT PASSWORD MODAL -->
    <div id="forgotPasswordModal" class="otp-modal">
        <div class="otp-card">
            <button type="button" id="closeForgotModal" class="close-btn">&times;</button>
            <h5 class="mb-3">Reset Password</h5>
            <form id="sendOtpForm">
                @csrf
                <div class="form-group">
                    <input type="email" name="email" id="resetEmail" class="form-control" placeholder="Enter your email" required>
                </div>
                <button type="submit" id="sendOtpBtn"  class="btn btn-primary btn-block">Send OTP</button>
            </form>
            <div id="forgotMessage" class="mt-3"></div>
        </div>
    </div>

    <!-- ADMIN PASSWORD RESET OTP MODAL -->
    <div id="adminOtpModal" class="otp-modal">
        <div class="otp-card">
            <button type="button" id="closeAdminOtpModal" class="close-btn">&times;</button>
            <h6>Please enter the Passcode to reset your password</h6>
            <small>Passcode has been sent to your email</small>
            
            <form id="adminOtpForm" method="POST" action="{{ route('admin.password.reset.otp.verify') }}">
                @csrf
                <input type="hidden" name="email" id="adminOtpEmail">
                <div class="d-flex justify-content-center mt-3">
                    @for($i = 0; $i < 6; $i++)
                        <input type="text" class="otp-input admin-otp-input" maxlength="1" data-index="{{ $i }}">
                    @endfor
                </div>
                <input type="hidden" name="otp" id="adminOtpHidden">
                <div class="form-group mt-3">
                    <input type="password" name="password" class="form-control" placeholder="New Password" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm Password" required>
                </div>
                <button type="button" class="btn-validate" id="adminValidateBtn">Reset Password</button>
            </form>
            <div id="adminOtpError" class="mt-2 text-danger"></div>
        </div>
    </div>

    <!-- REGULAR LOGIN OTP MODAL -->
    @if(session('otp_email') || $errors->has('otp'))
    <div id="loginOtpModal" class="otp-modal active">
        <div class="otp-card">
            <button id="closeLoginOtpModal" class="close-btn">&times;</button>
            <h6>Please enter the Passcode to verify your account</h6>
            <small>Passcode has been sent to <br>{{ session('otp_email') }}</small>
            <div id="otpError">
                @if($errors->has('otp')) 
                    {{ $errors->first('otp') }} 
                @endif
                @if(session('success')) 
                    {{ session('success') }} 
                @endif
            </div>
            <form id="loginOtpForm" method="POST" action="{{ route('otp.verify') }}">
                @csrf
                <div class="d-flex justify-content-center mt-3">
                    @for($i = 0; $i < 6; $i++)
                        <input type="text" class="otp-input login-otp-input" maxlength="1" data-index="{{ $i }}">
                    @endfor
                </div>
                <input type="hidden" name="otp" id="loginOtpHidden">
            </form>
            <button class="btn-validate" id="loginValidateBtn">Validate</button>
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
            // Show login OTP modal if active
            if ($('#loginOtpModal').hasClass('active')) {
                $('#loginOtpModal').show();
            }

            // Generic OTP input handler for all OTP inputs
            function initializeOtpInputs(inputsClass) {
                $(inputsClass).on('input', function() {
                    this.value = this.value.replace(/\D/g, '');
                    if (this.value && $(this).next(inputsClass).length) {
                        $(this).next(inputsClass).focus();
                    }
                });

                $(inputsClass).on('keydown', function(e) {
                    if (e.key === 'Backspace' && !this.value) {
                        $(this).prev(inputsClass).focus();
                    }
                });

                $(inputsClass).on('paste', function(e) {
                    e.preventDefault();
                    let paste = e.originalEvent.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6);
                    $(inputsClass).each(function(i) {
                        this.value = paste[i] || '';
                    });
                });
            }

            // Initialize both OTP input types
            initializeOtpInputs('.login-otp-input');
            initializeOtpInputs('.admin-otp-input');

            // Toggle password visibility
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

            // Forgot password modal handlers
            $('#forgotPasswordLink').click(function(e) {
                e.preventDefault();
                $('#forgotPasswordModal').addClass('active').show();
            });

            $('#closeForgotModal').click(function() {
                $('#forgotPasswordModal').removeClass('active').hide();
                $('#forgotMessage').html('');
                $('#resetEmail').val('');
            });

            // Close modals when clicking outside
            // $('.otp-modal').click(function(e) {
            //     if (e.target === this) {
            //         $(this).removeClass('active').hide();
            //     }
            // });

            // Send OTP for password reset
          $('#sendOtpForm').submit(function(e) {

    e.preventDefault();

    let btn = $('#sendOtpBtn');

    // Disable button
    btn.prop('disabled', true);

    // Change text
    btn.html(`
        <span class="spinner-border spinner-border-sm mr-1"></span>
        Sending...
    `);

    $.ajax({
        url: "{{ route('admin.password.reset.otp.send') }}",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            email: $('#resetEmail').val()
        },

        success: function(response) {

            $('#forgotMessage').html(
                '<div class="alert alert-success">' +
                response.message +
                '</div>'
            );

            setTimeout(function() {

                $('#forgotPasswordModal')
                    .removeClass('active')
                    .hide();

                $('#forgotMessage').html('');

                $('#adminOtpEmail')
                    .val($('#resetEmail').val());

                $('#adminOtpModal')
                    .addClass('active')
                    .show();

                $('#adminOtpError').html('');

                $('.admin-otp-input').val('');

            }, 1500);

        },

        error: function(xhr) {

            let error = xhr.responseJSON?.message || 'Something went wrong';

            $('#forgotMessage').html(
                '<div class="alert alert-danger">' +
                error +
                '</div>'
            );
        },

        complete: function() {

            // Re-enable button
            btn.prop('disabled', false);

            // Restore text
            btn.html('Send OTP');
        }
    });
});

            // Close admin OTP modal
            $('#closeAdminOtpModal').click(function() {
                $('#adminOtpModal').removeClass('active').hide();
            });

            // Admin OTP validation (password reset)
            $('#adminValidateBtn').click(function(e) {
                e.preventDefault();
                
                let otp = '';
                $('.admin-otp-input').each(function() {
                    otp += $(this).val();
                });
                
                if (otp.length !== 6) {
                    $('#adminOtpError').html('<div class="alert alert-danger">Please enter complete 6-digit OTP</div>');
                    return;
                }
                
                $('#adminOtpHidden').val(otp);
                
                // Validate password fields
                let newPassword = $('#adminOtpForm input[name="password"]').val();
                let confirmPassword = $('#adminOtpForm input[name="password_confirmation"]').val();
                
                if (newPassword !== confirmPassword) {
                    $('#adminOtpError').html('<div class="alert alert-danger">Passwords do not match</div>');
                    return;
                }
                
                if (newPassword.length < 6) {
                    $('#adminOtpError').html('<div class="alert alert-danger">Password must be at least 6 characters</div>');
                    return;
                }
                
                $('#adminOtpForm').submit();
            });

            // Login OTP validation
            $('#loginValidateBtn').click(function(e) {
                e.preventDefault();
                
                let otp = '';
                $('.login-otp-input').each(function() {
                    otp += $(this).val();
                });
                
                if (otp.length !== 6) {
                    $('#otpError').html('<div class="alert alert-danger">Please enter complete 6-digit OTP</div>');
                    return;
                }
                
                $('#loginOtpHidden').val(otp);
                $('#loginOtpForm').submit();
            });

            // Close login OTP modal
            $('#closeLoginOtpModal').click(function() {
                $('#loginOtpModal').removeClass('active').hide();
            });
        });
    </script>
</body>

</html>