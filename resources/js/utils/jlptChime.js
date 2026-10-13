// Bel penanda sesi 聴解: memutar rekaman bel JLPT asli (/audio/jlpt-bell.mp3).
// Bila berkas gagal dimuat/diputar, dipakai bel sintesis Web Audio (dua nada turun).
const BELL_URL = '/audio/jlpt-bell.mp3'
let bellAudio = null
let ctx = null

function getCtx() {
  if (ctx)
    return ctx
  const AC = window.AudioContext || window.webkitAudioContext

  if (!AC)
    return null
  ctx = new AC()

  return ctx
}

// Panggil sekali dari handler klik agar browser mengizinkan audio.
export function unlockChime() {
  getCtx()?.resume?.().catch(() => {})
  try {
    // Muat di awal supaya bel tidak telat saat dibutuhkan.
    if (!bellAudio) {
      bellAudio = new Audio(BELL_URL)
      bellAudio.preload = 'auto'
      bellAudio.load() // hanya pemanasan cache; pemutaran memakai elemen baru
    }
  }
  catch { /* abaikan */ }
}

function playFile() {
  // Elemen Audio baru tiap kali bunyi: memakai ulang satu elemen terbukti
  // membuat bel kedua gagal/jatuh ke bel sintesis di sebagian browser.
  return new Promise((resolve, reject) => {
    const a = new Audio(BELL_URL)
    let settled = false
    const done = (fn, v) => {
      if (settled)
        return
      settled = true
      clearTimeout(guard)
      fn(v)
    }
    const guard = setTimeout(() => done(resolve), 6000) // jaga-jaga 'ended' tak terpicu

    a.preload = 'auto'
    a.onended = () => done(resolve)
    a.onerror = () => {
      const er = a.error

      done(reject, new Error(`audio element error code=${er?.code} msg=${er?.message || '-'} src=${a.currentSrc} net=${a.networkState}`))
    }
    a.play().catch(e => done(reject, e))
  })
}

/** Bunyikan bel; resolve (ms ~1.8 dtk) setelah bel selesai. Tidak pernah reject. */
// Cadangan 1: ambil file dengan fetch lalu putar lewat Web Audio (tidak bergantung
// pada elemen <audio>, Content-Type, atau Range request server).
async function playFileViaWebAudio() {
  const ac = getCtx()

  if (!ac)
    throw new Error('no AudioContext')
  if (ac.state === 'suspended')
    await ac.resume()
  const res = await fetch(BELL_URL, { cache: 'force-cache' })

  if (!res.ok)
    throw new Error(`fetch ${res.status}`)
  const buf = await ac.decodeAudioData(await res.arrayBuffer())

  await new Promise(resolve => {
    const src = ac.createBufferSource()

    src.buffer = buf
    src.connect(ac.destination)
    src.onended = () => resolve()
    src.start()
  })
}

export function playChime() {
  return playFile()
    .catch((e) => {
      console.warn('[jlpt-bell] <audio> gagal, coba via Web Audio:', e)

      return playFileViaWebAudio()
    })
    .catch((e) => {
      console.warn('[jlpt-bell] file gagal, pakai bel sintesis:', e)

      return playSynth()
    })
}

function playSynth() {
  return new Promise(resolve => {
    const ac = getCtx()

    if (!ac) {
      resolve()

      return
    }

    const run = () => {
      const t0 = ac.currentTime + 0.05
      const master = ac.createGain()

      master.gain.value = 0.5
      master.connect(ac.destination)

      // [frekuensi, mulai (dtk), lama (dtk)] — nada tinggi lalu nada rendah
      ;[[880, 0, 0.9], [659.25, 0.55, 1.2]].forEach(([freq, at, len]) => {
        // nada dasar + overtone supaya terdengar seperti lonceng
        ;[[1, 1], [2.76, 0.25], [5.4, 0.1]].forEach(([mult, amp]) => {
          const osc = ac.createOscillator()
          const g = ac.createGain()

          osc.type = 'sine'
          osc.frequency.value = freq * mult
          g.gain.setValueAtTime(0.0001, t0 + at)
          g.gain.exponentialRampToValueAtTime(amp, t0 + at + 0.01)
          g.gain.exponentialRampToValueAtTime(0.0001, t0 + at + len)
          osc.connect(g).connect(master)
          osc.start(t0 + at)
          osc.stop(t0 + at + len + 0.05)
        })
      })

      setTimeout(resolve, 1900)
    }

    if (ac.state === 'suspended')
      ac.resume().then(run, () => resolve())
    else
      run()
  })
}
