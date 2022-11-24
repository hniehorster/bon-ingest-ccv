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
<section class="section" style="padding: 0px">
    <div class="container">
        <div class="columns">
            <div class="column is-full">
                <div class="card" style="background-color: transparent !important;">
                    <div class="card-content is-shadowless border-rounded" style="background-color: transparent !important; padding:15px 5px">
                        <div class="card-image" style="background-color: transparent !important;">
                            <figure class="image has-text-centered mb-5">
                                <img src="https://bon-core-files.ams3.digitaloceanspaces.com/bon_logo_red_300.webp" style="max-width: 80px; width: auto; height: auto; margin-right: 40px" />
                                <svg height="70" viewBox="0 0 110 70" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M29.1726 64.783C27.5707 64.783 26.1292 64.4739 24.848 63.8562C23.5663 63.2385 22.6243 62.4176 22.0215 61.3937L24.6219 59.4604C25.7335 61.0671 27.2881 61.8701 29.2857 61.8701C30.2467 61.8701 31.0283 61.6543 31.6317 61.2214C32.2345 60.7889 32.5362 60.2285 32.5362 59.54C32.5362 58.2866 31.5844 57.3335 29.6814 56.6801L27.4767 55.9385C25.8558 55.3916 24.6594 54.6899 23.887 53.8334C23.114 52.9773 22.7281 51.9136 22.7281 50.6424C22.7281 49.0362 23.3401 47.7518 24.5654 46.7895C25.79 45.8274 27.354 45.3464 29.2574 45.3464C31.8013 45.3464 33.8268 46.2026 35.3345 47.9149L33.045 50.007C32.0084 48.8417 30.7364 48.2591 29.2291 48.2591C28.3433 48.2591 27.5942 48.4537 26.982 48.8417C26.3695 49.2303 26.0634 49.76 26.0634 50.4305C26.0634 51.0839 26.294 51.6002 26.756 51.9797C27.2175 52.3595 28.0039 52.7435 29.1161 53.1315L31.1512 53.8465C32.696 54.394 33.8739 55.1089 34.6844 55.9914C35.4944 56.8745 35.8902 57.9953 35.8715 59.3545C35.8715 60.979 35.2497 62.2897 34.006 63.2869C32.7623 64.2844 31.1512 64.783 29.1726 64.783Z" fill="white"></path>
                                    <path d="M42.118 64.4657H39.0088V44.6052H42.118V53.026C43.0034 51.6491 44.4357 50.9606 46.4143 50.9606C48.0537 50.9606 49.3631 51.4637 50.3432 52.47C51.3227 53.4762 51.813 54.8179 51.813 56.495V64.4657H48.7038V56.9717C48.7038 55.9124 48.4396 55.0874 47.9124 54.4956C47.3848 53.9044 46.6687 53.6086 45.7642 53.6086C44.6901 53.6086 43.8139 53.9885 43.1355 54.7473C42.4572 55.5066 42.118 56.5744 42.118 57.9514V64.4657Z" fill="white"></path>
                                    <path d="M62.1863 62.0295C63.4109 62.0295 64.4237 61.6282 65.2249 60.8247C66.0256 60.0217 66.4261 59.0286 66.4261 57.8456C66.4261 56.6631 66.0256 55.67 65.2249 54.8666C64.4237 54.0634 63.4109 53.6618 62.1863 53.6618C60.9426 53.6618 59.9202 54.0634 59.1196 54.8666C58.3184 55.67 57.9182 56.6631 57.9182 57.8456C57.9182 59.0286 58.3184 60.0217 59.1196 60.8247C59.9202 61.6282 60.9426 62.0295 62.1863 62.0295ZM67.4579 62.7313C66.0348 64.0646 64.2779 64.7305 62.1863 64.7305C60.0946 64.7305 58.3372 64.0646 56.9149 62.7313C55.492 61.3987 54.7808 59.77 54.7808 57.8456C54.7808 55.9217 55.492 54.2932 56.9149 52.96C58.3372 51.6273 60.0946 50.9608 62.1863 50.9608C64.2779 50.9608 66.0348 51.6273 67.4579 52.96C68.8802 54.2932 69.5918 55.9217 69.5918 57.8456C69.5918 59.77 68.8802 61.3987 67.4579 62.7313Z" fill="white"></path>
                                    <path d="M79.8229 62.0825C81.0476 62.0825 82.0559 61.6766 82.8473 60.8644C83.6389 60.0527 84.0345 59.0464 84.0345 57.8456C84.0345 56.6453 83.6389 55.6391 82.8473 54.827C82.0559 54.0151 81.0476 53.6089 79.8229 53.6089C78.5792 53.6089 77.5617 54.0151 76.7703 54.827C75.979 55.6391 75.5831 56.6453 75.5831 57.8456C75.5831 59.0464 75.979 60.0527 76.7703 60.8644C77.5617 61.6766 78.5792 62.0825 79.8229 62.0825ZM75.8375 70.0002H72.7285V51.2254H75.8375V52.9996C76.2707 52.4349 76.8976 51.9537 77.7173 51.5565C78.5369 51.1593 79.4272 50.9608 80.3882 50.9608C82.3105 50.9608 83.9258 51.6318 85.2356 52.9733C86.5453 54.315 87.2002 55.939 87.2002 57.8456C87.2002 59.7522 86.5453 61.3767 85.2356 62.718C83.9258 64.0601 82.3105 64.7305 80.3882 64.7305C79.4272 64.7305 78.5369 64.532 77.7173 64.1348C76.8976 63.7376 76.2707 63.2568 75.8375 62.6917V70.0002Z" fill="white"></path>
                                    <path d="M109.374 8.91373C109.374 11.1469 107.442 12.9568 105.058 12.9568C102.675 12.9568 100.743 11.1469 100.743 8.91373C100.743 6.68071 102.675 4.87067 105.058 4.87067C107.442 4.87067 109.374 6.68071 109.374 8.91373Z" fill="white"></path>
                                    <path d="M83.8597 5.53455L96.5645 29.2891L91.4106 38.7813L73.5825 5.53467L83.8597 5.53455Z" fill="white"></path>
                                    <path d="M63.2258 25.6881C61.8302 28.3583 58.9023 30.1976 55.5149 30.1976C50.7754 30.1976 46.9332 26.598 46.9332 22.1577C46.9332 17.7176 50.7754 14.1181 55.5149 14.1181C58.9392 14.1181 61.894 15.9975 63.271 18.7151L71.4591 14.8572C68.5737 9.33646 62.5186 5.53442 55.5149 5.53442C45.7153 5.53442 37.771 12.9769 37.771 22.1577C37.771 31.3386 45.7153 38.7812 55.5149 38.7812C62.4695 38.7812 68.4887 35.032 71.3979 29.5739L63.2258 25.6881Z" fill="white"></path>
                                    <path d="M25.4723 25.6881C24.0768 28.3583 21.1489 30.1976 17.7615 30.1976C13.0219 30.1976 9.17978 26.598 9.17978 22.1577C9.17978 17.7176 13.0219 14.1181 17.7615 14.1181C21.1858 14.1181 24.1406 15.9975 25.5175 18.7151L33.7056 14.8572C30.8203 9.33646 24.7651 5.53442 17.7615 5.53442C7.96187 5.53442 0.0175781 12.9769 0.0175781 22.1577C0.0175781 31.3386 7.96187 38.7812 17.7615 38.7812C24.7161 38.7812 30.7353 35.032 33.6445 29.5739L25.4723 25.6881Z" fill="white"></path>
                                </svg>️
                            </figure>
                        </div>
                    </div>
                </div>
                <form method="POST" action="{{ route('finalizeInstall', ['apiLocale' => request()->get('apiLocale')]) }}" id="shopnumber-form">
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
                                {{--<p>
                                    <svg style="width:12px;height:12px" viewBox="0 0 24 24">
                                        <path fill="#0EAE86" d="M10,17L5,12L6.41,10.58L10,14.17L17.59,6.58L19,8M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2Z" />
                                    </svg>
                                    {{ __('pre_install.contract.fixed_fee') }}
                                </p>--}}
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
                            <input type="hidden" name="api_public" value="{{ $api_public }}" />
                            <input type="hidden" name="language" value="{{ $language }}" />
                            <input type="hidden" name="x_hash" value="{{ $x_hash }}" />
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
