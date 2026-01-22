<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-white">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6" enctype="multipart/form-data"
          x-data="{
            photoPreview: null,
            removePhoto: false,
            pickPhoto() { this.$refs.photo.click(); },
            photoChosen() {
              const file = this.$refs.photo.files?.[0];
              if (!file) return;
              this.removePhoto = false;
              const reader = new FileReader();
              reader.onload = (e) => { this.photoPreview = e.target?.result; };
              reader.readAsDataURL(file);
            },
            clearPhoto() {
              this.photoPreview = null;
              this.removePhoto = true;
              if (this.$refs.photo) this.$refs.photo.value = null;
            }
          }">
        @csrf
        @method('patch')

        <input type="hidden" name="remove_photo" :value="removePhoto ? 1 : 0">

        <div class="rounded-2xl border border-gray-100 dark:border-gray-700 bg-white/60 dark:bg-gray-900/20 p-5">
            <div class="flex items-center gap-5">
                @php
                    $currentPhotoUrl = $user->profile_photo_path ? asset('storage/' . $user->profile_photo_path) : null;
                    $initials = collect(preg_split('/\s+/', trim((string) $user->name)))
                        ->filter()
                        ->take(2)
                        ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))
                        ->implode('');
                    if ($initials === '') {
                        $initials = mb_strtoupper(mb_substr((string) $user->name, 0, 2));
                    }
                @endphp

                <div class="relative">
                    <div class="h-20 w-20 rounded-full ring-2 ring-blue-500/40 overflow-hidden bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                        <template x-if="photoPreview">
                            <img :src="photoPreview" alt="Foto Profil" class="h-full w-full object-cover">
                        </template>
                        <template x-if="!photoPreview && {{ $currentPhotoUrl ? 'true' : 'false' }} && !removePhoto && !imageError">
                            <img src="{{ $currentPhotoUrl }}" alt="Foto Profil" class="h-full w-full object-cover" x-on:error="imageError = true">
                        </template>
                        <template x-if="!photoPreview && (!{{ $currentPhotoUrl ? 'true' : 'false' }} || removePhoto || imageError)">
                            <div class="h-full w-full flex items-center justify-center bg-gradient-to-br from-blue-600 to-purple-600 text-white font-extrabold text-xl">
                                {{ $initials }}
                            </div>
                        </template>
                    </div>
                </div>

                <div class="flex-1 min-w-0">
                    <div class="font-semibold text-gray-900 dark:text-gray-100">Foto Profil</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Unggah foto untuk personalisasi akun Anda.</div>
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <input x-ref="photo" id="photo" name="photo" type="file" accept="image/*" class="hidden" @change="photoChosen()">
                        <button type="button" @click="pickPhoto()" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-sm font-semibold shadow-sm transition-colors">
                            Ubah Foto
                        </button>
                        <button type="button" @click="clearPhoto()" class="inline-flex items-center gap-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-800 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-100 px-4 py-2 text-sm font-semibold transition-colors"
                                x-show="photoPreview || ({{ $currentPhotoUrl ? 'true' : 'false' }} && !removePhoto)" style="display: none;">
                            Hapus Foto
                        </button>
                    </div>
                    <x-input-error class="mt-2" :messages="$errors->get('photo')" />
                </div>
            </div>
        </div>

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800 dark:text-gray-200">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:text-gray-300 dark:hover:text-white dark:focus:ring-offset-gray-800">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-gray-400"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
