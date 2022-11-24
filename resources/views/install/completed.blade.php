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

        .qr-code {
            background-color: #fff;
            padding: 20px 10px;
            margin: 0px auto;
            text-align: center;
            width: 300px;
            border-radius: 5px;
        }
        .codes {
            width: 300px;
            margin: 0px auto;
        }
    </style>
</head>
<body>
<section class="section" style="padding-top: 10px !important">
    <div class="container">
        <div class="columns">
            <div class="column is-full">
                <div class="card" style="background-color: transparent !important;">
                    <div class="card-content is-shadowless border-rounded" style="background-color: transparent !important; padding: 5px 24px 24px 24px">
                        <div class="card-image" style="background-color: transparent !important;">
                            <figure class="image has-text-centered mb-5">
                                <img src="https://bon-core-files.ams3.digitaloceanspaces.com/bon_logo_red_300.webp" style="max-width: 80px; width: auto; height: auto;" />
                            </figure>
                        </div>
                    </div>
                </div>
                <div class="card" style="border-radius: 10px;">
                    <div class="card-content is-shadowless border-rounded" style="border-radius: 10px;">
                        <div class="content">
                            <strong>{{ __('post_install.connect.header') }}</strong>
                            <span class="has-text-centered">
                                <hr width="95%" style="background-color: transparent !important; margin:15px auto; border-top: 3px solid #2A3140;" />
                            </span>
                            <p>
                                <span class="badge">1</span> {!! __('post_install.connect.step_1') !!} <br />
                            </p>
                            <p>
                                <span class="badge">2</span> {!! __('post_install.connect.step_2') !!} <br />
                            </p>
                            <p>
                                <span class="badge">3</span> {!! __('post_install.connect.step_3') !!} <br />
                            </p>
                            <p>
                                <span class="badge">4</span> {!! __('post_install.connect.step_4') !!} <br />
                            </p>
                            <p>
                                <span class="badge">5</span> {!! __('post_install.connect.step_5') !!} <br />
                            </p>
                            <p>
                                <span class="badge">6</span> {!! __('post_install.connect.step_6') !!} <br />
                            </p>
                            <div class="qr-code">
                                <img src="data:image/png;base64, {!! base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')->size(250)->generate('https://bonmerchant.page.link/merchant_download')) !!} ">
                            </div>
                            <div class="codes">
                                <div class="field">
                                    <div class="control">
                                        <input class="input" type="text" name="token_1" size="4" style="width:auto !important; text-align:center;" value="{{ $manualToken->token_1 }}" /> - <input class="input" type="text" name="token_2" size="4" style="width:auto !important; text-align: center;" value="{{ $manualToken->token_2 }}" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
</body>
</html>
