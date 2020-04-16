@extends('nova::auth.layout')

@section('content')

@include('nova::auth.partials.header')
    <div class="bg-white shadow rounded-lg p-8 max-w-login mx-auto">
        <form method="POST" action="{{ $auth->route('verify', $credentials) }}">
            {{ csrf_field() }}

            @component('nova::auth.partials.heading')
                {{ __("Please enter received code") }}
            @endcomponent 

            @if ($errors->any())
            <p class="text-center font-semibold text-danger my-3">
                {{ $errors->first('code') }}
            </p>
            @endif
         
            <div class="mb-6 {{ $errors->has('code') ? ' has-error' : '' }}">
                <label class="block font-bold mb-2" for="code">
                    {{ $field['label'] ?? 'code' }}
                </label>
                <input id="code" type="text" name="code" value="{{ old('code') }}" autofocus
                    class="form-control form-input w-full {{ $errors->has('code') ? 'border-danger' : 'form-input-bordered' }}">
            </div> 

            <div class="flex mb-6">
                <label class="flex items-center block text-xl font-bold">
                    <input class="" type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    <span class="text-base ml-2">{{ __('Remember Me') }}</span>
                </label>
            </div>
            <button class="w-full btn btn-default btn-primary hover:bg-primary-dark" type="submit">
                {{ __('Send') }}
            </button>

        </form>
        <div class="flex mt-6">  
            <span class="px-4 w-2/5 text-right">
                <a class="btn btn-link hover:text-success-dark text-success" 
                    href="javascript:void(0);" onclick="resend()">
                        {{ __('Resend') }}
                        <span id="counter"></span>
                </a>
            </span> 
            <span class="px-4 w-3/5 no-underline">
                <a class="btn btn-link no-underline text-danger hover:text-warning-dark" href="{{ $auth->route('verification') }}">
                    {{ __('Change Number') }}
                </a>
            </span> 
        </div>
    </div>
    <script type="text/javascript">
        var seconds = 60, interval = null, sending = false;

        function showSeconds(second) {
            document.getElementById('counter').innerHTML = (second > 0 ? second : ''); 
        }

        function countdown() {
            seconds = 60;

            interval = setInterval(function() { 
                showSeconds(seconds--);

                if(seconds < 1) { 
                    clearInterval(interval);
                }  
            }, 1000); 
        } 
        
        countdown();

        function resend() { 
            if(seconds > 0) { 
                var message = "{{ __("Wait :second second", ['second' => '__S__']) }}";

                return alert(message.replace('__S__', seconds));
            } else if(sending) {
                return;
            } 

            sending = true;

            clearInterval(interval);

            xhr = new XMLHttpRequest();

            xhr.open('POST', '{{ $auth->route('resend', $credentials) }}');
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) { 
                    countdown(); 
                }
                else if (xhr.status !== 200) {
                    alert('Request failed.  Returned status of ' + xhr.status);
                }

                sending = false;
            };
            xhr.send("_token={{ csrf_token() }}"); 
        }
    </script>
@endsection
