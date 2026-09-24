import { $api } from '@/utils/api'

/**
 * Biometric / passkey login (WebAuthn), on top of the browser's *native*
 * navigator.credentials API and the built-in
 * PublicKeyCredential.parse*OptionsFromJSON() / credential.toJSON() helpers
 * (Chrome/Edge 122+, Safari 18+, Firefox 122+) — no @github/webauthn-json
 * dependency needed; that package is deprecated upstream in favor of these
 * native methods. The backend is laravel/passkeys (laragear/webauthn is
 * abandoned upstream); both speak the same standard WebAuthn JSON shape
 * these native helpers expect, so the /api/webauthn/* endpoints didn't need
 * to change.
 */

export function isWebAuthnSupported() {
  return (
    typeof window !== 'undefined'
    && typeof window.PublicKeyCredential !== 'undefined'
    // Older browsers have PublicKeyCredential but not the JSON helpers.
    && typeof window.PublicKeyCredential.parseCreationOptionsFromJSON === 'function'
    && typeof window.PublicKeyCredential.parseRequestOptionsFromJSON === 'function'
  )
}

/** Register a new passkey for the currently signed-in user. */
export async function registerPasskey(alias = 'Passkey') {
  const options = await $api('/webauthn/register/options', { method: 'POST' })

  const credential = await navigator.credentials.create({
    publicKey: PublicKeyCredential.parseCreationOptionsFromJSON(options),
  })

  return $api('/webauthn/register', { method: 'POST', body: { ...credential.toJSON(), alias } })
}

/**
 * Sign in with a passkey. "Usernameless": the browser lets the person pick
 * from whichever passkey(s) it has stored for this site — no email typed.
 * Returns { user, token, expires_at }, same shape as a normal login.
 */
export async function loginWithPasskey() {
  const options = await $api('/webauthn/login/options', { method: 'POST' })

  const credential = await navigator.credentials.get({
    publicKey: PublicKeyCredential.parseRequestOptionsFromJSON(options),
  })

  return $api('/webauthn/login', { method: 'POST', body: credential.toJSON() })
}

export async function listPasskeys() {
  return $api('/webauthn/credentials')
}

export async function removePasskey(id) {
  return $api(`/webauthn/credentials/${id}`, { method: 'DELETE' })
}
