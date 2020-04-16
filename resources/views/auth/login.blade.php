@extends('nova::auth.layout')

@section('content')

@include('nova::auth.partials.header')

<form
    class="bg-white shadow rounded-lg p-8 max-w-login mx-auto"
    method="POST"
    action="{{ $auth->route('login') }}"
>
    {{ csrf_field() }}

    @component('nova::auth.partials.heading')
        {{ $auth->title() }}
    @endcomponent

    @if ($errors->any())
    <p class="text-center font-semibold text-danger my-3">
        @foreach(array_keys($auth->loginFields()) as $name)
            @if ($errors->has($name))
                {{ $errors->first($name) }} <br />
            @endif
        @endforeach
    </p>
    @endif

    @foreach($auth->loginFields() as $name => $field) 
        <div class="mb-6 {{ $errors->has($name) ? ' has-error' : '' }}">
            <label class="block font-bold mb-2" for="{{ $name }}">
                {{ $field['label'] ?? $name }}
            </label>
            <input 
                class="form-control form-input {{ $errors->has($name) ? 'border-danger' : 'form-input-bordered' }} w-full" 
                id="{{ $name }}" type="{{ $field['type'] ?? 'text' }}" name="{{ $name }}" 
                value="{{ old($name) }}" {{ !$loop->first ?? 'autofocus' }}>
        </div>
    @endforeach 

    <div class="flex mb-6">
        <label class="flex items-center block text-xl font-bold">
            <input class="" type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
            <span class="text-base ml-2">{{ __('Remember Me') }}</span>
        </label>


        @if (\Laravel\Nova\Nova::resetsPasswords())
        <div class="ml-auto">
            <a class="text-primary dim font-bold no-underline" href="{{ route('nova.password.request') }}">
                {{ __('Forgot Your Password?') }}
            </a>
        </div>
        @endif
    </div>

    <button class="w-full btn btn-default btn-primary hover:bg-primary-dark" type="submit">
        {{ __('Login') }}
    </button>
</form>
@endsection
