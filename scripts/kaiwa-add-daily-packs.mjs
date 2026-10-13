// Menambahkan paket situasi harian (kereta, konbini, pabrik, restoran, rumahsakit, telepon, supermarket, pakaian, gaji, keitai, bank, kelurahan, tetangga) ke public/data/kaiwa/index.json → situations.
// Aman dijalankan berulang (entri dengan id yang sudah ada dilewati; entri lain tidak disentuh).
//   node scripts/kaiwa-add-daily-packs.mjs
import fs from 'node:fs'
import path from 'node:path'
import { fileURLToPath } from 'node:url'

const dir = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../public/data/kaiwa')
const indexFile = path.join(dir, 'index.json')

const NEW = [
  {
    id: 'kereta',
    file: 'situation-kereta.json',
    track: 'situation',
    icon: 'tabler-train',
    level: 'N5',
    title_id: 'Kereta & Stasiun',
    title_en: 'Trains & Stations',
    desc_id: '8 situasi di stasiun dan kereta: beli tiket, cari peron, transfer, kartu IC, barang tertinggal, kereta terlambat.',
    desc_en: '8 situations at stations and on trains: buying tickets, finding platforms, transfers, IC cards, lost items, delays.',
  },
  {
    id: 'konbini',
    file: 'situation-konbini.json',
    track: 'situation',
    icon: 'tabler-shopping-bag',
    level: 'N5',
    title_id: 'Konbini (Toserba 24 Jam)',
    title_en: 'Convenience Store (Konbini)',
    desc_id: '8 situasi di konbini: beli bento, bayar, cari barang, fotokopi, kirim paket, bayar tagihan, kembalian salah, camilan panas.',
    desc_en: '8 situations at a konbini: bento, paying, finding items, copying, sending parcels, paying bills, wrong change, hot snacks.',
  },
  {
    id: 'pabrik',
    file: 'situation-pabrik.json',
    track: 'situation',
    icon: 'tabler-building-factory-2',
    level: 'N4',
    title_id: 'Pabrik (Tempat Kerja)',
    title_en: 'Factory (Workplace)',
    desc_id: '12 situasi kerja di pabrik: pagi hari, penjelasan kerja, keselamatan, barang cacat, mesin rusak, izin, terlambat, cuti, pulang kerja, gempa & evakuasi, dan tempat pengungsian.',
    desc_en: '12 workplace situations at a factory: morning start, instructions, safety, defects, machine trouble, absences, lateness, leave, clocking out, earthquake & evacuation, and the evacuation shelter.',
  },
  {
    id: 'restoran',
    file: 'situation-restoran.json',
    track: 'situation',
    icon: 'tabler-tools-kitchen-2',
    level: 'N5',
    title_id: 'Restoran',
    title_en: 'Restaurant',
    desc_id: '8 situasi di restoran: masuk & dapat meja, memesan, pantangan makanan, pesanan salah, minta rekomendasi, bayar, reservasi telepon, restoran cepat saji.',
    desc_en: '8 restaurant situations: getting a table, ordering, food restrictions, wrong order, recommendations, paying, phone booking, fast food.',
  },
  {
    id: 'rumahsakit',
    file: 'situation-rumahsakit.json',
    track: 'situation',
    icon: 'tabler-stethoscope',
    level: 'N4',
    title_id: 'Rumah Sakit',
    title_en: 'Hospital',
    desc_id: '8 situasi di rumah sakit: mendaftar, demam & batuk, sakit perut, alergi & pemeriksaan, apotek, ubah jadwal, memanggil ambulans, surat dokter.',
    desc_en: '8 hospital situations: registering, fever and cough, stomach ache, allergies and tests, pharmacy, rescheduling, calling an ambulance, doctor\'s note.',
  },
  {
    id: 'telepon',
    file: 'situation-telepon.json',
    track: 'situation',
    icon: 'tabler-phone',
    level: 'N4',
    title_id: 'Telepon',
    title_en: 'Telephone',
    desc_id: '8 situasi lewat telepon: menelepon perusahaan, meninggalkan pesan, salah sambung, menerima telepon, paket ulang, menelepon pengelola, terlambat, suara tidak jelas.',
    desc_en: '8 phone situations: calling a company, leaving a message, wrong number, answering a call, parcel re-delivery, calling the manager, running late, a bad line.',
  },
  {
    id: 'supermarket',
    file: 'situation-supermarket.json',
    track: 'situation',
    icon: 'tabler-shopping-cart',
    level: 'N5',
    title_id: 'Supermarket',
    title_en: 'Supermarket',
    desc_id: '8 situasi di supermarket: cari barang, bahan makanan & label halal, daging dan ikan, harga & diskon, kasir, barang habis, lauk siap saji, menyimpan makanan.',
    desc_en: '8 supermarket situations: finding items, ingredients & halal labels, meat and fish, prices & discounts, the register, out-of-stock items, ready-made food, storing food.',
  },
  {
    id: 'pakaian',
    file: 'situation-pakaian.json',
    track: 'situation',
    icon: 'tabler-shirt',
    level: 'N5',
    title_id: 'Belanja Pakaian',
    title_en: 'Clothes Shopping',
    desc_id: '8 situasi di toko pakaian: mencari baju, ukuran (サイズ), warna, mencoba (試着), pas atau tidak, harga & obral, sepatu, tukar barang.',
    desc_en: '8 clothing store situations: looking for clothes, sizes, colors, trying on (試着), fit, prices & sales, shoes, exchanges.',
  },
  {
    id: 'gaji',
    file: 'situation-gaji.json',
    track: 'situation',
    icon: 'tabler-receipt-yen',
    level: 'N4',
    title_id: 'Gaji & Slip Gaji',
    title_en: 'Pay & Payslip',
    desc_id: '7 situasi seputar gaji: hari gajian & rekening, membaca slip gaji, uang lembur, potongan, koreksi jam lembur, koreksi potongan ganda, gaji belum masuk.',
    desc_en: '7 pay situations: payday & account, reading a payslip, overtime pay, deductions, correcting overtime hours, correcting a double deduction, pay not received.',
  },
  {
    id: 'keitai',
    file: 'situation-keitai.json',
    track: 'situation',
    icon: 'tabler-device-mobile',
    level: 'N4',
    title_id: 'Ponsel & Internet (携帯・契約)',
    title_en: 'Phone & Internet (携帯・契約)',
    desc_id: '8 situasi kontrak ponsel dan internet: mulai kontrak, memilih paket, SIM card, cara bayar, bertanya tagihan, internet rumah, ponsel hilang, berhenti langganan.',
    desc_en: '8 situations about phone and internet contracts: starting a contract, choosing a plan, SIM cards, payment, asking about bills, home internet, a lost phone, cancelling.',
  },
  {
    id: 'bank',
    file: 'situation-bank.json',
    track: 'situation',
    icon: 'tabler-building-bank',
    level: 'N4',
    title_id: 'Bank & Kirim Uang',
    title_en: 'Bank & Sending Money',
    desc_id: '8 situasi di bank: membuka rekening, PIN & buku tabungan, memakai ATM, kirim uang ke Indonesia (送金), tanya biaya & kurs, uang belum sampai, kartu ATM hilang, kartu tertelan.',
    desc_en: '8 bank situations: opening an account, PIN & passbook, using an ATM, sending money to Indonesia (送金), fees & exchange rate, money not arrived, lost ATM card, card stuck.',
  },
  {
    id: 'kelurahan',
    file: 'situation-kelurahan.json',
    track: 'situation',
    icon: 'tabler-building-community',
    level: 'N4',
    title_id: 'Kelurahan & Imigrasi',
    title_en: 'City Hall & Immigration',
    desc_id: '10 situasi di kantor kelurahan dan imigrasi: cari loket, lapor alamat, 住民票, mengisi formulir, kartu hilang, perpanjangan, pindah kerja, dan minta penjelasan sebelum tanda tangan.',
    desc_en: '10 situations at city hall and immigration: finding the counter, address registration, 住民票, forms, a lost card, extensions, changing jobs, and asking for an explanation before signing.',
  },
  {
    id: 'tetangga',
    file: 'situation-tetangga.json',
    track: 'situation',
    icon: 'tabler-home-heart',
    level: 'N4',
    title_id: 'Tetangga & Asrama',
    title_en: 'Neighbors & Dormitory',
    desc_id: '8 situasi tetangga dan asrama: menyapa tetangga baru, hari buang sampah, memilah sampah, minta maaf karena berisik, menegur dengan sopan, kunci hilang, terkunci di luar, mesin cuci bersama.',
    desc_en: '8 neighbor and dorm situations: greeting neighbors, garbage days, sorting garbage, apologizing for noise, politely complaining, a lost key, being locked out, the shared washing machine.',
  },
]

const raw = fs.readFileSync(indexFile, 'utf8')
const crlf = raw.includes('\r\n')
const index = JSON.parse(raw)

index.situations ??= []
const added = []

for (const entry of NEW) {
  if (!fs.existsSync(path.join(dir, entry.file))) {
    console.error(`LEWATI ${entry.id}: ${entry.file} belum ada di ${dir}`)
    continue
  }
  if (index.situations.some(s => s.id === entry.id))
    continue
  index.situations.push(entry)
  added.push(entry.id)
}

let out = `${JSON.stringify(index, null, 2)}\n`

if (crlf)
  out = out.replace(/\n/g, '\r\n')
if (added.length)
  fs.writeFileSync(indexFile, out)

console.log(added.length ? `Ditambahkan: ${added.join(', ')}` : 'Tidak ada yang perlu ditambahkan.')
console.log(`Paket situasi sekarang: ${index.situations.map(s => s.id).join(', ')}`)
