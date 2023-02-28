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


        <h4 class="py-2">Cybersecurity PDF Report - Reject</h4>
        <h5>You received the opportunity to get a cybersecurity PDF report for:<br/>
            <div class="font-semibold mt-3">{{$pdfRequest->item}}</div>
        </h5>
        <h4>But it seems you want to reject it. Submit the form to proceed.</h4>

        <form class="w-auto py-4" method="POST"
              action="{{route('pdf-request.reject-post',['token' => $pdfRequest->verification->token, 'hash'=>$pdfRequest->getHash()])}}">

            {{csrf_field()}}
            <input type="checkbox" value="1"/> Reject the PDF Report
            <br/>
            <button type="submit" class="ring-1 bg-indigo-50 px-2 mt-6">I'm sure</button>

        </form>
        <p class="small">

            If instead you want to get the Cybersecurity PDF report request, please click
            <a href="{{route('pdf-request.verify',['token' => $pdfRequest->verification->token, 'hash'=>$pdfRequest->getHash()])}}">here</a>.
        </p>
    </div>
</div>
</body>
</html>
