@extends('layouts.master')

    @section('title')
    <title>Gestion de stock | Liste des utilisateurs</title>
    @endsection

    @section('style')
    @include('partials.style')
    @endsection

    @section('content')
        <main>
            <header class="page-header page-header-dark bg-gradient-primary-to-secondary pb-10">
                <div class="container-xl px-4">
                    <div class="page-header-content pt-4">
                        <div class="row align-items-center justify-content-between">
                            <div class="col-auto mt-4">
                                <h1 class="page-header-title">
                                    <div class="page-header-icon"><i data-feather="activity"></i></div>
                                    CREATION DE COMPTE UTILISATEUR
                                </h1>
                            </div>
                            <div class="col-12 col-xl-auto mt-4">
                                <div class="input-group input-group-joined border-0" style="width: 16.5rem">
                                    <span class="input-group-text"><i class="text-primary" data-feather="calendar"></i></span>
                                    <div  class="form-control ps-0 pointer" >
                                        {{Carbon\Carbon::now()->format('d-m-Y')}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            <!-- Main page content-->
            <div class="container-xl px-4 mt-n10">
                <div class="card">
                    <div class="card-header">Formulaire d'inscription</div>
                    <div class="card-body">
                        <x-guest-layout>
                            <x-auth-card>
                                
                                <x-slot name="logo">
                                    
                                    <a href="/">
                                    <img class="text-center" src="{{ asset('asset/logo-bg.png') }}" alt="" width="150px" style="margin: auto">
                                     <h1 class="text-center" style="font-size: 30px;font-weight:900">Gestion de stock</h1>
                                    </a>
                                    <h1 class="text-center" style="font-size: 20px;font-weight:600">Création d'un nouvel utilisateur</h1>
                                    
                                </x-slot>
                                

                                <!-- Validation Errors -->
                                <x-auth-validation-errors class="mb-4" :errors="$errors" />

                                <form method="POST" action="{{ route('register') }}">
                                    @csrf

                                    <!-- Name -->
                                    <div>
                                        <x-label for="name" :value="__('Name')" />

                                        <x-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                                    </div>

                                    <!-- Email Address -->
                                    <div class="mt-4">
                                        <x-label for="email" :value="__('Email')" />

                                        <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required />
                                    </div>

                                    <!-- Password -->
                                    <div class="mt-4">
                                        <x-label for="password" :value="__('Password')" />

                                        <x-input id="password" class="block mt-1 w-full"
                                                        type="password"
                                                        name="password"
                                                        required autocomplete="new-password" />
                                    </div>

                                    <!-- Confirm Password -->
                                    <div class="mt-4">
                                        <x-label for="password_confirmation" :value="__('Confirm Password')" />

                                        <x-input id="password_confirmation" class="block mt-1 w-full"
                                                        type="password"
                                                        name="password_confirmation" required />
                                    </div>

                                    <div class="flex items-center justify-end mt-4">
                                        

                                        <x-button class="ml-4">
                                            Créer un compte
                                        </x-button>
                                    </div>
                                </form>
                            </x-auth-card>
                        </x-guest-layout>         
                    </div>
                </div>
            </div>

        </main>
    @endsection





































































