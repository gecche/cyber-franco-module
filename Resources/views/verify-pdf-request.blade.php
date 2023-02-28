<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    @if (isset($favicon))
        <link rel="icon" type="image/x-icon" href="{{ $favicon }}">
    @else
        <link rel="icon" type="image/x-icon" href="favicon.ico">
    @endif

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

</head>
<body class="font-sans antialiased">
<div class="min-h-screen bg-gray-100">
{{--    @dump($pdfRequest->toArray())--}}

    <div class="text-center border mx-auto" style="width:500px;">


        <h4 class="py-2">Cybersecurity PDF Report</h4>
        <h5>Accepting this form, you will receive the cybersecurity PDF report for:<br/>
            <div class="font-semibold mt-3">{{$pdfRequest->item}}</div>
        </h5>

        <form class="w-auto py-4" method="POST"
              action="{{route('pdf-request.verify-post',['token' => $pdfRequest->verification->token, 'hash'=>$pdfRequest->getHash()])}}">

            {{csrf_field()}}
            <input type="checkbox" value="1"/> Accept terms and conditions
            <br/>
            <button type="submit" class="ring-1 bg-indigo-50 px-2 mt-6">Start processing PDF Report</button>

        </form>
        <p class="small">

            If you want to cancel the Cybersecurity PDF report request, please click
            <a href="{{route('pdf-request.reject',['token' => $pdfRequest->verification->token, 'hash'=>$pdfRequest->getHash()])}}">here</a>.
        </p>
    </div>
</div>
</body>
</html>
