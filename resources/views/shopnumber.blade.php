<meta charset="UTF-8">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.3/css/bulma.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma-extensions@4.0.0/dist/css/bulma-extensions.min.css" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"   integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4="   crossorigin="anonymous"></script>
    <title>Confirm your platform</title>
</head>
<body>
<section class="section pt-1 pb-1">
    <div class="container">
        <div class="columns">
            <div class="column is-full">
                <div class="card mb-3">
                    <div class="card-header">
                        <p class="card-header-title">
                            {{ __('pre_install.header') }}
                        </p>
                    </div>
                    <form method="POST" action="{{ route('redirectPage', ['user_uuid' => $user_uuid, 'apiLocale' => $apiLocale]) }}" id="shopnumber-form">

                    <div class="card-content">
                        <div class="content">
                            <div class="field">
                                <div class="control">
                                    <input class="input" type="text" name="shop_number" placeholder="213123">
                                </div>
                            </div>
                                <div class="field is-grouped">
                                    <div class="control">
                                        <button class="button is-link" name="submit" value="formSubmit" type="submit">Submit</button>
                                    </div>
                                </div>
                        </div>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
</body>
</html>
