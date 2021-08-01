<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">

        <title>Laravel</title>
    </head>
    <body>

            <p style="color:#BADA55; gr">This is so bad ass</p>
            <div class="bg-white pt-4">

                <h1 class="font-bold text-4xl mb-6 text-center">Metrinize recipe</h1>
                <div class="px-4">
                    <form action="/" method="get">
                        <input
                            class="px-4 py-2 border-solid border-2 border-blue-400 rounded-md w-full"
                            type="text"
                            name="url"
                            value="{{$oldUrl}}"
                        >
                        <button class="px-4 py-2 mt-4 bg-blue-400 text-white rounded-md" type="submit">Go</button>
                    </form>
                </div>

                <div id="result">
                    <h1 class="font-bold text-2xl mb-6 text-center">Recipe ingredients</h1>
                </div>
        </div>
    </body>
</html>
