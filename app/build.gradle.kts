plugins {
    id("com.android.application")
    id("org.jetbrains.kotlin.android")
}

android {
    namespace = "id.go.tulungagung.dishub.angkutan"
    compileSdk = 35

    defaultConfig {
        applicationId = "id.go.tulungagung.dishub.angkutan"
        minSdk = 23
        targetSdk = 35
        versionCode = (providers.environmentVariable("GITHUB_RUN_NUMBER").orNull ?: "1").toInt()
        versionName = providers.environmentVariable("APP_VERSION_NAME").orNull ?: "1.0.0"
    }

    signingConfigs {
        create("release") {
            val storeFilePath = providers.environmentVariable("ANDROID_KEYSTORE_PATH").orNull
            if (storeFilePath != null) {
                storeFile = file(storeFilePath)
                storePassword = providers.environmentVariable("ANDROID_KEYSTORE_PASSWORD").orNull
                keyAlias = providers.environmentVariable("ANDROID_KEY_ALIAS").orNull
                keyPassword = providers.environmentVariable("ANDROID_KEY_PASSWORD").orNull
            }
        }
    }

    buildTypes {
        release {
            signingConfig = signingConfigs.getByName("release")
            isMinifyEnabled = false
            proguardFiles(getDefaultProguardFile("proguard-android-optimize.txt"), "proguard-rules.pro")
        }
    }
}

dependencies {
    implementation("androidx.core:core-ktx:1.15.0")
    implementation("androidx.appcompat:appcompat:1.7.0")
}
