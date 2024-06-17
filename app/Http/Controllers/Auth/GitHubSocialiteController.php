<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Laravel\Socialite\Facades\Socialite;

class GitHubSocialiteController extends Controller
{
    public function redirectToProvider(): RedirectResponse|Redirect
    {
        if (! config('services.github.client_id') || ! config('services.github.client_secret')) {
            Log::debug('GitHub login is not enabled. Redirecting back to login.');

            return Redirect::route('login')->with('loginError', 'GitHub login is not enabled.');
        }

        return Socialite::driver('github')
            ->scopes(['read:user'])
            ->redirect();
    }

    public function handleProviderCallback(): RedirectResponse
    {
        try {
            $githubUser = Socialite::driver('github')->user();

            if ($user = $this->findUserByGitHubId($githubUser->getId())) {
                return $this->loginAndRedirect($user, 'Found GH ID associated with this user, logging them in.');
            }

            if ($user = $this->findUserByEmailAndUpdateGitHubId($githubUser)) {
                return $this->loginAndRedirect($user, 'Adding the user\'s GH ID to their account.');
            }

            return $this->createUserAndLogin($githubUser);

        } catch (Exception $e) {
            Log::error('GitHub OAuth login error: ' . $e->getMessage());

            return Redirect::route('login')->with('error', 'Authentication failed. There may be an error with GitHub. Please try again later.');
        }
    }

    private function findUserByGitHubId(string $githubId): ?User
    {
        return User::where('github_id', $githubId)->first();
    }

    private function findUserByEmailAndUpdateGitHubId($githubUser): ?User
    {
        $user = User::where('email', $githubUser->getEmail())->first();

        $user?->update(['github_id' => $githubUser->getId()]);

        return $user;
    }

    private function createUserAndLogin($githubUser): RedirectResponse
    {
        $user = User::create([
            'name' => $githubUser->getName(),
            'email' => $githubUser->getEmail(),
            'github_id' => $githubUser->getId(),
        ]);

        Log::debug('Creating new user with their GitHub ID and logging them in.', ['id' => $githubUser->getId()]);
        Auth::login($user);

        return Redirect::route('overview');
    }

    private function loginAndRedirect(User $user, string $message): RedirectResponse
    {
        Log::debug($message, ['id' => $user->github_id]);
        Auth::login($user);

        return Redirect::route('overview');
    }
}