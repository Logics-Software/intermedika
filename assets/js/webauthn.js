const WebAuthnHelper = {
  // Fungsi utama untuk login biometrik
  authenticateBiometric: function (username) {
    return new Promise((resolve, reject) => {
      // 1. DETEKSI: Apakah aplikasi berjalan di Android WebView kita?
      if (window.Android && window.Android.authenticateBiometric) {
        console.log("Android Bridge detected: Menggunakan Native Biometric");

        // 2. PERSIAPAN: Buat fungsi penerima (callback) yang akan dipanggil Android nanti
        window.biometricCallback = function (result) {
          console.log("Android callback received:", result);

          if (result.success) {
            // Jika Android bilang sukses, kita selesaikan Promise ini
            resolve(result);
          } else {
            // Jika Android bilang gagal, kita lempar error
            reject(
              new Error(result.error || "Authentication failed from Android")
            );
          }
        };

        // 3. EKSEKUSI: Panggil fungsi Java di Android
        window.Android.authenticateBiometric(username, "biometricCallback");
      } else {
        // 4. FALLBACK: Jika dibuka di Chrome/Laptop biasa (Bukan App Android)
        console.log(
          "Android Bridge NOT detected: Menggunakan WebAuthn Standar"
        );

        // WebAuthn implementation for regular browsers
        this.authenticateWebAuthn(username).then(resolve).catch(reject);
      }
    });
  },

  // Fungsi Register (Opsional, jika dipakai)
  registerBiometric: function () {
    return new Promise((resolve, reject) => {
      if (window.Android && window.Android.registerBiometric) {
        window.biometricRegisterCallback = function (result) {
          if (result.success) resolve(result);
          else reject(new Error(result.error));
        };
        window.Android.registerBiometric("biometricRegisterCallback");
      } else {
        // WebAuthn implementation for regular browsers
        this.registerWebAuthn().then(resolve).catch(reject);
      }
    });
  },

  // Fungsi Cek Credential (Opsional)
  hasCredentials: function (username) {
    return new Promise((resolve, reject) => {
      if (window.Android && window.Android.hasCredentials) {
        window.hasCredentialsCallback = function (result) {
          resolve(result.hasCredentials);
        };
        window.Android.hasCredentials(username, "hasCredentialsCallback");
      } else {
        resolve(false);
      }
    });
  },

  // Convert base64url to ArrayBuffer
  base64UrlToArrayBuffer: function (base64url) {
    const base64 = base64url.replace(/-/g, "+").replace(/_/g, "/");
    const binaryString = window.atob(base64);
    const bytes = new Uint8Array(binaryString.length);
    for (let i = 0; i < binaryString.length; i++) {
      bytes[i] = binaryString.charCodeAt(i);
    }
    return bytes.buffer;
  },

  // Convert ArrayBuffer to base64url
  arrayBufferToBase64Url: function (buffer) {
    const bytes = new Uint8Array(buffer);
    let binary = "";
    for (let i = 0; i < bytes.byteLength; i++) {
      binary += String.fromCharCode(bytes[i]);
    }
    const base64 = window.btoa(binary);
    return base64.replace(/\+/g, "-").replace(/\//g, "_").replace(/=/g, "");
  },

  // Check if WebAuthn is supported (including native biometric in WebView)
  isSupported: function () {
    // If we're in WebView, check if native biometric interface is available
    if (this.isWebView()) {
      return this.isNativeBiometricAvailable();
    }

    // For regular browsers, check WebAuthn support
    return typeof window.PublicKeyCredential !== "undefined";
  },

  // Check if native biometric interface is available
  isNativeBiometricAvailable: function () {
    // Check Android interface
    if (typeof Android !== "undefined" && Android.authenticateBiometric) {
      return true;
    }

    // Check iOS interface
    if (
      typeof webkit !== "undefined" &&
      webkit.messageHandlers &&
      webkit.messageHandlers.biometric
    ) {
      return true;
    }

    return false;
  },

  // Detect if running in Android WebView
  isAndroidWebView: function () {
    const userAgent = navigator.userAgent;
    // Check for Android WebView indicators
    return (
      /Android/.test(userAgent) &&
      (/wv/.test(userAgent) || /Version\/\d+\.\d+/.test(userAgent)) &&
      !/Chrome\/[.0-9]*/.test(userAgent)
    );
  },

  // Check if running in any WebView (iOS or Android)
  isWebView: function () {
    const userAgent = navigator.userAgent;
    // Android WebView
    if (this.isAndroidWebView()) {
      return true;
    }
    // iOS WebView (WKWebView or UIWebView)
    if (/iPhone|iPad/.test(userAgent)) {
      return (
        !userAgent.includes("Safari/") ||
        (userAgent.includes("WebKit") && !userAgent.includes("Version/"))
      );
    }
    return false;
  },

  // Register biometric
  registerBiometric: function () {
    return new Promise(async (resolve, reject) => {
      if (window.Android && window.Android.registerBiometric) {
        console.log("Android Bridge detected for registration");

        // Setup callback
        window.biometricRegisterCallback = function (result) {
          console.log("Android register callback received", result);
          if (result.success) {
            resolve(result);
          } else {
            reject(new Error(result.error || "Biometric registration failed"));
          }
        };

        // Call Android native registration
        window.Android.registerBiometric("biometricRegisterCallback");
      } else {
        // WebAuthn implementation for regular browsers
        try {
          if (!this.isSupported()) {
            throw new Error(
              "WebAuthn tidak didukung di browser ini. Pastikan menggunakan browser modern (Chrome, Firefox, Edge, Safari)"
            );
          }

          // Get registration options from server
          const response = await fetch("/api/webauthn/registration/start", {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
            },
            credentials: "include",
          });

          const data = await response.json();

          if (!data.success) {
            throw new Error(data.error || "Gagal memulai registrasi");
          }

          const options = data.options;

          // Convert challenge to ArrayBuffer
          options.challenge = this.base64UrlToArrayBuffer(options.challenge);
          options.user.id = this.base64UrlToArrayBuffer(options.user.id);

          // Convert allowCredentials if exists
          if (options.allowCredentials) {
            options.allowCredentials = options.allowCredentials.map((cred) => ({
              ...cred,
              id: this.base64UrlToArrayBuffer(cred.id),
            }));
          }

          // Create credential
          const credential = await navigator.credentials.create({
            publicKey: options,
          });

          // Convert credential to JSON for sending to server
          const publicKeyArrayBuffer = credential.response.getPublicKey
            ? credential.response.getPublicKey()
            : null;

          const credentialJSON = {
            id: credential.id,
            rawId: this.arrayBufferToBase64Url(credential.rawId),
            type: credential.type,
            response: {
              clientDataJSON: this.arrayBufferToBase64Url(
                credential.response.clientDataJSON
              ),
              attestationObject: this.arrayBufferToBase64Url(
                credential.response.attestationObject
              ),
            },
          };

          // Add publicKey if available
          if (publicKeyArrayBuffer) {
            credentialJSON.response.publicKey =
              this.arrayBufferToBase64Url(publicKeyArrayBuffer);
          }

          // Send to server
          const completeResponse = await fetch(
            "/api/webauthn/registration/complete",
            {
              method: "POST",
              headers: {
                "Content-Type": "application/json",
              },
              credentials: "include",
              body: JSON.stringify({
                credential: credentialJSON,
              }),
            }
          );

          const completeData = await completeResponse.json();

          if (!completeData.success) {
            throw new Error(
              completeData.error || "Gagal menyelesaikan registrasi"
            );
          }

          resolve(completeData);
        } catch (error) {
          console.error("WebAuthn registration error:", error);
          reject(error);
        }
      }
    });
  },

  // List credentials - for WebView, return mock data based on hasCredentials
  listCredentials: function () {
    return new Promise(async (resolve, reject) => {
      try {
        if (this.isWebView()) {
          // For WebView, check if user has credentials and return mock data
          const hasCredentials = await this.hasCredentials("current_user");
          if (hasCredentials) {
            // Return mock credential data
            resolve([
              {
                credential_id: "webview_biometric_credential",
                created_at: new Date().toISOString(),
                last_used_at: null,
                name: "Biometric Credential",
              },
            ]);
          } else {
            resolve([]);
          }
        } else {
          // For regular browsers, call server API
          const response = await fetch("/api/webauthn/credentials", {
            method: "GET",
            credentials: "include",
          });

          const data = await response.json();
          resolve(data.success ? data.credentials : []);
        }
      } catch (error) {
        console.error("Error listing credentials:", error);
        resolve([]); // Return empty array instead of rejecting to prevent UI issues
      }
    });
  },

  // Delete credential
  deleteCredential: function (credentialId) {
    return new Promise(async (resolve, reject) => {
      try {
        if (this.isWebView()) {
          // For WebView, we can't really delete individual credentials
          // Just return success for now
          resolve(true);
        } else {
          // For regular browsers, call server API
          const response = await fetch("/api/webauthn/credentials/delete", {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
            },
            credentials: "include",
            body: JSON.stringify({ credential_id: credentialId }),
          });

          const data = await response.json();
          resolve(data.success);
        }
      } catch (error) {
        console.error("Error deleting credential:", error);
        resolve(false);
      }
    });
  },

  // WebAuthn authentication for regular browsers
  authenticateWebAuthn: async function (username) {
    if (!this.isSupported()) {
      throw new Error("WebAuthn tidak didukung di browser ini");
    }

    try {
      // Get authentication options from server
      const response = await fetch("/api/webauthn/authentication/start", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        credentials: "include",
        body: JSON.stringify({ username }),
      });

      const data = await response.json();

      if (!data.success) {
        throw new Error(data.error || "Gagal memulai autentikasi");
      }

      const options = data.options;

      // Convert challenge to ArrayBuffer
      options.challenge = this.base64UrlToArrayBuffer(options.challenge);

      // Convert allowCredentials
      if (options.allowCredentials && options.allowCredentials.length > 0) {
        options.allowCredentials = options.allowCredentials.map((cred) => ({
          ...cred,
          id: this.base64UrlToArrayBuffer(cred.id),
        }));
      }

      // Get credential
      const credential = await navigator.credentials.get({
        publicKey: options,
      });

      // Convert credential to JSON
      const credentialJSON = {
        id: credential.id,
        rawId: this.arrayBufferToBase64Url(credential.rawId),
        type: credential.type,
        response: {
          authenticatorData: this.arrayBufferToBase64Url(
            credential.response.authenticatorData
          ),
          clientDataJSON: this.arrayBufferToBase64Url(
            credential.response.clientDataJSON
          ),
          signature: this.arrayBufferToBase64Url(credential.response.signature),
          userHandle: credential.response.userHandle
            ? this.arrayBufferToBase64Url(credential.response.userHandle)
            : null,
        },
      };

      // Send to server
      const completeResponse = await fetch(
        "/api/webauthn/authentication/complete",
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          credentials: "include",
          body: JSON.stringify({
            credential: credentialJSON,
          }),
        }
      );

      const completeData = await completeResponse.json();

      if (!completeData.success) {
        throw new Error(completeData.error || "Autentikasi gagal");
      }

      return completeData;
    } catch (error) {
      console.error("WebAuthn authentication error:", error);
      throw error;
    }
  },

  // WebAuthn registration for regular browsers
  registerWebAuthn: async function () {
    if (!this.isSupported()) {
      throw new Error(
        "WebAuthn tidak didukung di browser ini. Pastikan menggunakan browser modern (Chrome, Firefox, Edge, Safari)"
      );
    }

    try {
      // Get registration options from server
      const response = await fetch("/api/webauthn/registration/start", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        credentials: "include",
      });

      const data = await response.json();

      if (!data.success) {
        throw new Error(data.error || "Gagal memulai registrasi");
      }

      const options = data.options;

      // Convert challenge to ArrayBuffer
      options.challenge = this.base64UrlToArrayBuffer(options.challenge);
      options.user.id = this.base64UrlToArrayBuffer(options.user.id);

      // Convert allowCredentials if exists
      if (options.allowCredentials) {
        options.allowCredentials = options.allowCredentials.map((cred) => ({
          ...cred,
          id: this.base64UrlToArrayBuffer(cred.id),
        }));
      }

      // Create credential
      const credential = await navigator.credentials.create({
        publicKey: options,
      });

      // Convert credential to JSON for sending to server
      const publicKeyArrayBuffer = credential.response.getPublicKey
        ? credential.response.getPublicKey()
        : null;

      const credentialJSON = {
        id: credential.id,
        rawId: this.arrayBufferToBase64Url(credential.rawId),
        type: credential.type,
        response: {
          clientDataJSON: this.arrayBufferToBase64Url(
            credential.response.clientDataJSON
          ),
          attestationObject: this.arrayBufferToBase64Url(
            credential.response.attestationObject
          ),
        },
      };

      // Add publicKey if available
      if (publicKeyArrayBuffer) {
        credentialJSON.response.publicKey =
          this.arrayBufferToBase64Url(publicKeyArrayBuffer);
      }

      // Send to server
      const completeResponse = await fetch(
        "/api/webauthn/registration/complete",
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          credentials: "include",
          body: JSON.stringify({
            credential: credentialJSON,
          }),
        }
      );

      const completeData = await completeResponse.json();

      if (!completeData.success) {
        throw new Error(completeData.error || "Gagal menyelesaikan registrasi");
      }

      return completeData;
    } catch (error) {
      console.error("WebAuthn registration error:", error);
      throw error;
    }
  },
};
