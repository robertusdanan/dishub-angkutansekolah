# Android release

App name: **Angkutan Dishub Tulungagung**

Android shell loads production Laravel app at `https://dishubangkutan.kaventara.id/`. Business logic stays server-side, so web and Android keep same features.

## GitHub Actions secrets

Add these repository secrets before first release workflow run:

- `ANDROID_KEYSTORE_BASE64`
- `ANDROID_KEYSTORE_PASSWORD`
- `ANDROID_KEY_ALIAS`
- `ANDROID_KEY_PASSWORD`

Generate one permanent upload/release keystore and keep backup offline. Never commit keystore or passwords.

Workflow: `.github/workflows/android-release.yml`

- `app-release.aab`: upload to Google Play Console.
- `app-release.apk`: install for internal device testing.
- Runs on every push to `main` and manual dispatch.

## Important Play Store setup

1. Create app in Play Console with package ID `id.go.tulungagung.dishub.angkutan`.
2. Upload first signed AAB.
3. Complete Data safety, privacy policy, app access, and camera/location declarations.
4. Google OAuth must allow the production Android WebView flow. If Google blocks embedded sign-in, migrate only login to system browser/Custom Tab; do not weaken OAuth security.

`versionCode` uses GitHub Actions run number. `versionName` defaults to `1.0.0` and can be changed with `APP_VERSION_NAME` in workflow env.
