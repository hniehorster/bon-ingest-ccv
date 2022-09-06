<meta charset="UTF-8">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://bon-core-files.ams3.digitaloceanspaces.com/bon_main.css" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
    <title>Confirm your platform</title>
    <style>
        .border-rounded{
            border-radius: 10px;
            -webkit-border-radius: 10px;
        }
        label:hover, label:active, input:hover+label, input:active+label {
            color: #efffff !important;
        }
        strong { color: #fff;}
        p { margin: 5px 0 0 0}
        .small { font-size: 0.50em}
        input.apple-switch {
            position: relative;
            -webkit-appearance: none;
            outline: none;
            width: 50px;
            height: 30px;
            background-color: #fff;
            border: 1px solid #2A3140;
            border-radius: 50px;
            box-shadow: inset -20px 0 0 0 #2A3140;
        }

        input.apple-switch:after {
            content: "";
            position: absolute;
            top: 1px;
            left: 1px;
            background: transparent;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            box-shadow: 2px 4px 6px rgba(0,0,0,0.2);
        }

        input.apple-switch:checked {
            box-shadow: inset 20px 0 0 0 #4ed164;
            border-color: #4ed164;
        }

        input.apple-switch:checked:after {
            left: 20px;
            box-shadow: -2px 4px 3px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>
<section class="section" style="padding-top: 10px !important">
    <div class="container">
        <div class="columns">
            <div class="column is-full">
                <div class="card" style="background-color: transparent !important;">
                    <div class="card-content is-shadowless border-rounded" style="background-color: transparent !important;">
                        <div class="card-image" style="background-color: transparent !important;">
                            <figure class="image has-text-centered mb-5">
                                <img src="https://bon-core-files.ams3.digitaloceanspaces.com/bon_logo_red_300.webp" style="max-width: 80px; width: auto; height: auto;" />
                            </figure>
                        </div>
                    </div>
                </div>
                <form method="POST" action="{{ route('redirectPage', ['user_uuid' => $user_uuid, 'apiLocale' => $apiLocale]) }}" id="shopnumber-form">
                    <div class="card" style="border-radius: 10px;">
                        <div class="card-content is-shadowless border-rounded" style="border-radius: 10px;">
                             <div class="content">
                                <strong>{{ __('pre_install.contract.header') }}</strong>
                                <span class="has-text-centered">
                                    <hr width="95%" style="background-color: transparent !important; margin:15px auto; border-top: 3px solid #2A3140;" />
                                </span>
                                <p>
                                    <svg style="width:12px;height:12px" viewBox="0 0 24 24">
                                        <path fill="#0EAE86" d="M10,17L5,12L6.41,10.58L10,14.17L17.59,6.58L19,8M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2Z" />
                                    </svg>
                                    14-day Free Trail
                                </p>

                                <p>
                                    <svg style="width:12px;height:12px" viewBox="0 0 24 24">
                                        <path fill="#0EAE86" d="M10,17L5,12L6.41,10.58L10,14.17L17.59,6.58L19,8M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2Z" />
                                    </svg>
                                    No monthly costs
                                </p>

                                <p>
                                    <svg style="width:12px;height:12px" viewBox="0 0 24 24">
                                        <path fill="#0EAE86" d="M10,17L5,12L6.41,10.58L10,14.17L17.59,6.58L19,8M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2Z" />
                                    </svg>
                                    We boost your sales!
                                </p>
                                <span class="has-text-centered">
                                    <hr width="95%" style="background-color: transparent !important; margin:15px auto; border-top: 3px solid #2A3140;" />
                                </span>
                                <strong>{{ __('pre_install.contract.bon_generates_order') }}</strong>
                                <p>
                                    <svg style="width:12px;height:12px" viewBox="0 0 24 24">
                                        <path fill="#0EAE86" d="M10,17L5,12L6.41,10.58L10,14.17L17.59,6.58L19,8M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2Z" />
                                    </svg>
                                    {{ __('pre_install.contract.1st_purchase') }}
                                </p>
                                <p>
                                    <svg style="width:12px;height:12px" viewBox="0 0 24 24">
                                        <path fill="#0EAE86" d="M10,17L5,12L6.41,10.58L10,14.17L17.59,6.58L19,8M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2Z" />
                                    </svg>
                                    {{ __('pre_install.contract.2nd_purchase') }}
                                </p>
                                <p>
                                    <svg style="width:12px;height:12px" viewBox="0 0 24 24">
                                        <path fill="#0EAE86" d="M10,17L5,12L6.41,10.58L10,14.17L17.59,6.58L19,8M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2Z" />
                                    </svg>
                                    {{ __('pre_install.contract.3rd_purchase') }}
                                </p>
                                <p>
                                    <svg style="width:12px;height:12px" viewBox="0 0 24 24">
                                        <path fill="#0EAE86" d="M10,17L5,12L6.41,10.58L10,14.17L17.59,6.58L19,8M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2Z" />
                                    </svg>
                                    {{ __('pre_install.contract.fixed_fee') }}
                                </p>
                                <p class="small">{{ __('pre_install.contract.vat_notice') }}</p>
                                <span class="has-text-centered">
                                    <hr width="95%" style="background-color: transparent !important; margin:15px auto; border-top: 3px solid #2A3140;" />
                                </span>

                                <label class="checkbox">
                                    <input class="apple-switch" name="terms" type="checkbox" id="terms-switch" style="float:left; margin-right: 10px;">
                                    {!!  __('pre_install.contract.terms') !!} <span style="color:#E9665B;">*</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="card" style="background-color: transparent !important;">
                        <div class="card-content is-shadowless border-rounded" style="background-color: transparent !important;">
                            <input type="hidden" name="shop_number" value="{{ $shop_number }}" />
                            <input type="hidden" name="user_uuid" value="{{ $user_uuid }}" />
                            <button type="submit" id="confirmContract" class="button is-primary has-text-weight-bold">{{ __('pre_install.contract.submit') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
<script>
    $( document ).ready(function() {

        $("#modal-launcher, #modal-background, #modal-close").click(function () {
            console.log('Modal Launcher clicked');
            $("#modal-content, #modal-background").toggleClass("active");
        });

        $('.apple-switch').click(function () {
            var checkVal = $('.apple-switch').is(":checked");

            if(checkVal) {
                checkVal.checked = !checkVal.checked;
            }else{
                checkVal.checked = checkVal.checked;
            }
        })

        $('#confirmContract').click(function (e) {
            var checkVal = $('.apple-switch').is(":checked");

            if(!checkVal){
                e.preventDefault();

                $('.checkbox').animate({backgroundColor: '#fb4f48'}, 'slow');
                $('.checkbox').animate({backgroundColor: '#101828'}, 'slow');
            }
        });
    });
</script>
</body>
</html>
