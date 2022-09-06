<meta charset="UTF-8">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://bon-core-files.ams3.digitaloceanspaces.com/bon_main.css" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <title>Confirm your platform</title>
    <style>
        #modal-background {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: white;
            opacity: .1;
            -webkit-opacity: .5;
            -moz-opacity: .5;
            filter: alpha(opacity=50);
            z-index: 1000;
        }
        strong { color: #fff;}
        p { margin: 5px 0 0 0}
        #modal-content {
            background-color: white;
            border-radius: 10px;
            color: #20294d;
            -webkit-border-radius: 10px;
            display: none;
            left: 50%;
            margin: -120px 0 0 -160px;
            padding: 10px;
            position: fixed;
            top: 50%;
            width: 320px;
            z-index: 1000;
        }

        .helpImage {
            border-radius: 10px;
            -webkit-border-radius: 10px;
        }

        #modal-background.active, #modal-content.active {
            display: block;
        }
    </style>
</head>
<body>
<section class="section pt-1 pb-1">
    <div class="container">
        <div class="columns">
            <div class="column is-full">
                <div class="card" style="background-color: transparent !important;">
                    <div class="card-content is-shadowless border-rounded" style="background-color: transparent !important;">
                        <div class="card-image" style="background-color: transparent !important;">
                            <figure class="image has-text-centered mb-5">
                                <img src="https://bon-business-images.ams3.digitaloceanspaces.com/lightspeed_800.png" style="max-width: 200px; width: auto; height: auto;" />
                            </figure>
                        </div>
                    </div>
                </div>
                <form method="POST" action="{{ route('confirmSubscription', ['user_uuid' => $user_uuid, 'apiLocale' => $apiLocale]) }}" id="shopnumber">
                    <div class="card" style="border-radius: 10px;">

                            <div class="card-content is-shadowless border-rounded" style="border-radius: 10px;">
                                <div class="content">
                                    <strong>{{ __('pre_install.header') }}</strong>
                                    <div class="field">
                                        <div class="control">
                                            <input class="input" type="text" name="shop_number" placeholder="213123">
                                        </div>
                                    </div>
                                    <p>
                                        <a style="font-weight: normal;" id="modal-launcher">{{ __('pre_install.help_button') }}</a>
                                    </p>
                                </div>
                            </div>
                    </div>
                    <div class="card" style="background-color: transparent !important;">
                        <div class="card-content is-shadowless border-rounded" style="background-color: transparent !important;">
                            <button type="submit" form="shopnumber" value="submit" class="button is-primary has-text-weight-bold">{{ __('pre_install.submit') }}</button>
                        </div>
                    </div>
                </form>

                <div id="modal-background"></div>
                <div id="modal-content">
                    <p><strong>{{ __('pre_install.help_title') }}</strong></p>
                    <p>
                        {{ __('pre_install.help_text') }}
                        <img src="https://bon-core-files.ams3.digitaloceanspaces.com/lightspeed_shop_id.png" alt="Shop ID" class="helpImage" style="margin: 15px 0px;"/>
                    </p>
                    <button id="modal-close" class="button is-primary has-text-weight-bold">{{ __('pre_install.close_help') }}</button>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
    $(document).ready(
        $(function(){
            $("#modal-launcher, #modal-background, #modal-close").click(function() {
                console.log('Modal Launcher clicked');
                $("#modal-content, #modal-background").toggleClass("active");
            });
        })
    );
</script>
</body>
</html>
