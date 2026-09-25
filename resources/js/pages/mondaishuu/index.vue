<script setup>
// Mondaishuu — latihan tertulis per pelajaran (gaya "buku soal" seperti
// standar problem-set N5), disusun ulang dengan konten orisinal sendiri:
// pola dan cakupan tata bahasa tiap pelajaran diamati dari kurikulum
// Minna no Nihongo I (25 pelajaran + rangkuman), lalu ditiru bentuk
// latihannya dan dimodifikasi total kalimat/kosakatanya supaya bukan hasil
// scan/salin. Halaman berdiri sendiri, tanpa backend baru — mengikuti pola
// kana/kanji/lampiran/kosakata.
const { t } = useI18n()

// ---------------------------------------------------------------------
// Data latihan. Tiap pelajaran: beberapa "section" (meniru struktur buku:
// latihan partikel, latihan pola kalimat, latihan tanya-jawab), tiap
// section punya beberapa soal bernomor dengan kunci jawaban tersembunyi.
// ---------------------------------------------------------------------
const LESSONS = [
  { id: 1, focus: 'N wa N desu / kore・sore・are / no (kepemilikan)', sections: [
    { label: 'A. Lengkapi dengan kore / sore / are', items: [
      { q: '(benda dekat saya) ___ wa tas saya.', a: 'Kore' },
      { q: '(benda dekat lawan bicara) ___ wa buku Anda?', a: 'Sore' },
      { q: '(benda jauh dari keduanya) ___ wa apa itu?', a: 'Are' },
    ] },
    { label: 'B. Lengkapi partikel yang tepat', items: [
      { q: 'Watashi ( ) Rina desu.', a: 'wa' },
      { q: 'Kore wa watashi ( ) kasa desu.', a: 'no' },
      { q: 'Ano kata ( ) donata desu ka.', a: 'wa' },
    ] },
    { label: 'C. Jawab singkat', items: [
      { q: 'Tanaka-san wa gakusei desu ka. (jawab: ya)', a: 'Hai, gakusei desu.' },
      { q: 'Kore wa hon desu ka, zasshi desu ka. (jawab: buku)', a: 'Hon desu.' },
    ] },
  ] },
  { id: 2, focus: 'kono/sono/ano N, koko・soko・asoko, N no N (asal/jenis)', sections: [
    { label: 'A. Lengkapi kono/sono/ano + kata benda', items: [
      { q: '(dekat saya) ___ kaban wa watashi no desu.', a: 'Kono' },
      { q: '(dekat lawan bicara) ___ jisho wa nan no jisho desu ka.', a: 'Sono' },
      { q: '(jauh) ___ tatemono wa toshokan desu.', a: 'Ano' },
    ] },
    { label: 'B. Lengkapi koko/soko/asoko/dochira', items: [
      { q: 'Uketsuke wa ___ desu ka. (di mana, sopan)', a: 'dochira' },
      { q: 'Toire wa ___ desu. (di sana, dekat lawan bicara)', a: 'soko' },
    ] },
    { label: 'C. Buat kalimat N no N', items: [
      { q: 'Gabungkan: kamera / Jepang (kamera buatan Jepang)', a: 'Nihon no kamera' },
      { q: 'Gabungkan: sensei / Furansugo (guru bahasa Perancis)', a: 'Furansugo no sensei' },
    ] },
  ] },
  { id: 3, focus: 'Bilangan (harga), kono/sono/ano, doko no', sections: [
    { label: 'A. Tulis cara baca harga', items: [
      { q: '¥5,300', a: 'gosen sanbyaku en' },
      { q: '¥18,900', a: 'ichiman hassen kyuuhyaku en' },
      { q: '¥240,000', a: 'nijuuyonman en' },
    ] },
    { label: 'B. Lengkapi partikel', items: [
      { q: 'Sono tokei wa doko ( ) desu ka. …Suisu ( ) desu.', a: 'no / no' },
      { q: 'Sumimasen, sono kaban ( ) misete kudasai.', a: 'wo' },
    ] },
  ] },
  { id: 4, focus: 'Jam & menit, ni/kara/made, hari libur', sections: [
    { label: 'A. Tulis cara baca jam', items: [
      { q: '3:15', a: 'sanji juugofun' },
      { q: '9:40', a: 'kuji yonjuppun' },
      { q: '11:50', a: 'juuichiji gojuppun' },
    ] },
    { label: 'B. Lengkapi ni/kara/made', items: [
      { q: 'Ginkou wa 9-ji ( ) 3-ji ( ) desu.', a: 'kara / made' },
      { q: 'Mainichi 7-ji ( ) okimasu.', a: 'ni' },
    ] },
  ] },
  { id: 5, focus: 'Tanggal, kata kerja lampau, e/to (arah & bersama)', sections: [
    { label: 'A. Tulis cara baca tanggal', items: [
      { q: '5/14', a: 'gogatsu juuyokka' },
      { q: '9/20', a: 'kugatsu hatsuka' },
      { q: '1/1', a: 'ichigatsu tsuitachi' },
    ] },
    { label: 'B. Ubah ke bentuk lampau', items: [
      { q: 'Kyoto e ikimasu → kemarin (ikimashita)', a: 'Kinou Kyoto e ikimashita.' },
      { q: 'Nihon e kimasu → tahun lalu (kimashita)', a: 'Kyonen Nihon e kimashita.' },
    ] },
    { label: 'C. Lengkapi partikel', items: [
      { q: 'Tomodachi ( ) eiga wo mimasu.', a: 'to' },
      { q: 'Ashita gakkou ( ) ikimasen.', a: 'e' },
    ] },
  ] },
  { id: 6, focus: 'Kata kerja bentuk masu, partikel wo/ni/de/to', sections: [
    { label: 'A. Lengkapi partikel', items: [
      { q: 'Mainichi kohii ( ) nomimasu.', a: 'wo' },
      { q: 'Kouen ( ) sanpo shimasu.', a: 'de' },
      { q: 'Sensei ( ) nihongo ( ) naraimasu.', a: 'ni / wo' },
    ] },
    { label: 'B. Jawab pertanyaan (bentuk negatif)', items: [
      { q: 'Kinou benkyou shimashita ka. (jawab: tidak)', a: 'Iie, benkyou shimasen deshita.' },
      { q: 'Tabako wo suimasu ka. (jawab: tidak)', a: 'Iie, suimasen.' },
    ] },
  ] },
  { id: 7, focus: 'de (alat), ni (penerima), agemasu/moraimasu', sections: [
    { label: 'A. Lengkapi partikel', items: [
      { q: 'Hashi ( ) gohan wo tabemasu.', a: 'de' },
      { q: 'Tanjoubi ni tomodachi ( ) purezento wo moraimashita.', a: 'ni' },
    ] },
    { label: 'B. Ubah agemasu ⇄ moraimasu', items: [
      { q: 'Watashi wa Sari-san ni hana wo agemashita. → (dari sudut pandang Sari)', a: 'Sari-san wa watashi ni hana wo moraimashita.' },
    ] },
  ] },
  { id: 8, focus: 'Kata sifat i / na, lawan kata', sections: [
    { label: 'A. Tulis lawan katanya', items: [
      { q: 'atarashii (baru) ⇔', a: 'furui' },
      { q: 'yasui (murah) ⇔', a: 'takai' },
      { q: 'shizuka (tenang) ⇔', a: 'nigiyaka' },
    ] },
    { label: 'B. Gabungkan kata sifat + kata benda', items: [
      { q: '<kaban・atarashii>', a: 'Atarashii kaban wo kaimashita.' },
      { q: '<sensei・shinsetsu>', a: 'Watt-san wa shinsetsu na sensei desu.' },
    ] },
  ] },
  { id: 9, focus: 'suki/kirai (ga), kara (alasan)', sections: [
    { label: 'A. Lengkapi partikel', items: [
      { q: 'Watashi wa sakana ( ) suki desu.', a: 'ga' },
      { q: 'Atsui desu ( ), mado wo akemasu.', a: 'kara' },
    ] },
    { label: 'B. Jawab dengan alasan (kara)', items: [
      { q: 'Doushite benkyou shimasu ka. (alasan: ujian besok)', a: 'Ashita shiken ga arimasu kara.' },
    ] },
  ] },
  { id: 10, focus: 'arimasu/imasu, posisi (ue/shita/naka/mae)', sections: [
    { label: 'A. Pilih arimasu atau imasu', items: [
      { q: 'Tsukue no ue ni hon ga ( ).', a: 'arimasu' },
      { q: 'Niwa ni neko ga ( ).', a: 'imasu' },
    ] },
    { label: 'B. Lengkapi posisi', items: [
      { q: 'Neko wa hako no ( ) ni imasu. (di dalam)', a: 'naka' },
      { q: 'Tokei wa tsukue no ( ) ni arimasu. (di atas)', a: 'ue' },
    ] },
  ] },
  { id: 11, focus: 'Kata bantu bilangan (counter)', sections: [
    { label: 'A. Isi jumlah dengan counter yang tepat', items: [
      { q: 'Ringo wo ( ) kudasai. (4 buah)', a: 'yottsu' },
      { q: 'Kitte wo ( ) kudasai. (3 lembar)', a: 'sanmai' },
      { q: 'Kazoku wa ( ) desu. (5 orang)', a: 'gonin' },
    ] },
  ] },
  { id: 12, focus: 'Perbandingan (yori, no naka de ichiban)', sections: [
    { label: 'A. Buat kalimat perbandingan', items: [
      { q: 'Kereta / bus / cepat (kereta lebih cepat)', a: 'Densha wa basu yori hayai desu.' },
      { q: 'Olahraga / sepak bola / paling seru', a: 'Supootsu de sakkaa ga ichiban omoshiroi desu.' },
    ] },
  ] },
  { id: 13, focus: 'hoshii, ~tai, ni ikimasu (tujuan)', sections: [
    { label: 'A. Ubah ke bentuk ~tai', items: [
      { q: 'nomimasu (minum) → saya ingin minum kopi', a: 'Kohii wo nomitai desu.' },
      { q: 'kaimasu (beli) → ingin beli komputer baru', a: 'Atarashii pasokon ga hoshii desu.' },
    ] },
    { label: 'B. Buat kalimat tujuan (ni ikimasu)', items: [
      { q: 'perpustakaan / pinjam buku', a: 'Toshokan e hon wo karini ikimasu.' },
    ] },
  ] },
  { id: 14, focus: 'Bentuk te (permintaan, ~te kudasai)', sections: [
    { label: 'A. Ubah ke bentuk te', items: [
      { q: 'kakimasu →', a: 'kaite' },
      { q: 'yomimasu →', a: 'yonde' },
      { q: 'kimasu →', a: 'kite' },
    ] },
    { label: 'B. Buat kalimat permintaan', items: [
      { q: 'jendela / buka', a: 'Mado wo akete kudasai.' },
    ] },
  ] },
  { id: 15, focus: 'Bentuk te imasu (sedang, keadaan, pekerjaan)', sections: [
    { label: 'A. Ubah ke ~te imasu', items: [
      { q: 'Ima nani wo shimasu ka → (sedang membaca koran)', a: 'Shinbun wo yonde imasu.' },
      { q: 'Doko ni sunde imasu ka → (tinggal di Bandung)', a: 'Bandung ni sunde imasu.' },
    ] },
  ] },
  { id: 16, focus: 'Rangkaian aksi (~te, ~te kara)', sections: [
    { label: 'A. Gabungkan dua kalimat dengan te', items: [
      { q: 'Mado wo shimemasu + heya wo demasu', a: 'Mado wo shimete, heya wo demasu.' },
      { q: 'Shukudai wo shimasu + terebi wo mimasu (pakai ~te kara)', a: 'Shukudai wo shite kara, terebi wo mimasu.' },
    ] },
  ] },
  { id: 17, focus: 'nakereba narimasen / nakutemo ii / naide kudasai', sections: [
    { label: 'A. Ubah sesuai konteks', items: [
      { q: 'benkyou shimasu → (harus)', a: 'benkyou shinakereba narimasen' },
      { q: 'shinpai shimasu → (tidak perlu, boleh tidak)', a: 'shinpai shinakutemo ii desu' },
      { q: 'shashin wo torimasu → (larangan, jangan)', a: 'shashin wo toranaide kudasai' },
    ] },
  ] },
  { id: 18, focus: 'koto ga dekimasu, bentuk kamus', sections: [
    { label: 'A. Ubah ke bentuk kamus', items: [
      { q: 'tabemasu →', a: 'taberu' },
      { q: 'hanashimasu →', a: 'hanasu' },
    ] },
    { label: 'B. Buat kalimat kemampuan', items: [
      { q: 'saya / berenang 50 meter', a: 'Watashi wa 50-meetoru oyogu koto ga dekimasu.' },
    ] },
  ] },
  { id: 19, focus: 'koto ga arimasu (pengalaman), ~tari ~tari', sections: [
    { label: 'A. Buat kalimat pengalaman', items: [
      { q: 'pernah naik Fuji-san', a: 'Fujisan ni nobotta koto ga arimasu.' },
    ] },
    { label: 'B. Gabungkan dengan ~tari ~tari', items: [
      { q: 'baca buku + nonton TV (kegiatan akhir pekan)', a: 'Hon wo yondari, terebi wo mitari shimasu.' },
    ] },
  ] },
  { id: 20, focus: 'Bentuk santai (plain form) dalam percakapan', sections: [
    { label: 'A. Ubah ke bentuk santai', items: [
      { q: 'Wakarimasu ka →', a: 'Wakaru?' },
      { q: 'Ikimasen deshita →', a: 'Ikanakatta.' },
    ] },
  ] },
  { id: 21, focus: '~to omoimasu, ~to iimashita, deshou', sections: [
    { label: 'A. Buat kalimat pendapat', items: [
      { q: 'besok hujan (menurut saya)', a: 'Ashita ame ga furu to omoimasu.' },
      { q: 'dia bilang akan datang', a: 'Kare wa kuru to iimashita.' },
    ] },
  ] },
  { id: 22, focus: 'Klausa penjelas kata benda (kalimat + N)', sections: [
    { label: 'A. Gabungkan jadi frasa benda', items: [
      { q: 'orang itu memakai topi merah → orang yang...', a: 'akai boushi wo kabutte iru hito' },
      { q: 'buku yang saya beli kemarin', a: 'kinou katta hon' },
    ] },
  ] },
  { id: 23, focus: '~toki (ketika)', sections: [
    { label: 'A. Buat kalimat dengan toki', items: [
      { q: 'lelah / istirahat', a: 'Tsukareta toki, yasumimasu.' },
      { q: 'kecil (dulu) / suka menggambar', a: 'Kodomo no toki, e wo kaku no ga suki deshita.' },
    ] },
  ] },
  { id: 24, focus: 'agemasu/moraimasu/kuremasu (untuk orang lain)', sections: [
    { label: 'A. Pilih kata yang tepat', items: [
      { q: 'Ibu memberikan payung kepada saya: haha wa watashi ni kasa wo ( ).', a: 'kuremashita' },
      { q: 'Saya memberi bunga kepada Rina: watashi wa Rina-san ni hana wo ( ).', a: 'agemashita' },
    ] },
  ] },
  { id: 25, focus: 'Kalimat pengandaian ~tara, ~temo', sections: [
    { label: 'A. Buat kalimat pengandaian', items: [
      { q: 'ada waktu / mampir', a: 'Jikan ga attara, yotte kudasai.' },
      { q: 'hujan / tetap pergi (temo)', a: 'Ame ga futtemo, ikimasu.' },
    ] },
  ] },
]

const REVIEWS = [
  { id: 'r1-8', label: 'Rangkuman 1–8', items: [
    { q: 'Kore wa watashi ( ) kaban desu.', a: 'no' },
    { q: 'Mainichi 6-ji ( ) okimasu.', a: 'ni' },
    { q: 'Tulis: ¥3,700', a: 'sanzen nanahyaku en' },
    { q: 'Kinou Bandung ( ) ikimashita.', a: 'e' },
    { q: 'Lawan kata: yasui ⇔', a: 'takai' },
  ] },
  { id: 'r9-17', label: 'Rangkuman 9–17', items: [
    { q: 'Watashi wa kudamono ( ) suki desu.', a: 'ga' },
    { q: 'Neko wa isu no ( ) ni imasu. (bawah)', a: 'shita' },
    { q: 'Densha wa basu ( ) hayai desu. (perbandingan)', a: 'yori' },
    { q: 'nomimasu → bentuk te', a: 'nonde' },
    { q: 'benkyou shimasu → harus', a: 'benkyou shinakereba narimasen' },
  ] },
  { id: 'r18-25', label: 'Rangkuman 18–25', items: [
    { q: 'oyogimasu → bentuk kamus', a: 'oyogu' },
    { q: 'Fujisan ni nobotta ( ) ga arimasu. (pengalaman)', a: 'koto' },
    { q: 'Ashita ame ga furu ( ) omoimasu.', a: 'to' },
    { q: 'Tsukareta ( ), hayaku nemasu. (ketika)', a: 'toki' },
    { q: 'Jikan ga at( ), asobi ni kite kudasai. (pengandaian)', a: 'tara' },
  ] },
  { id: 'r1-25', label: 'Rangkuman 1–25', items: [
    { q: 'Ano kata wa ( ) desu ka. (siapa, sopan)', a: 'donata' },
    { q: 'Tulis: 7:45', a: 'shichiji yonjuugofun' },
    { q: 'Watashi wa Rina-san ni CD wo ( ). (meminjam dari Rina)', a: 'karimashita' },
    { q: 'Mado wo ( ) kudasai. (tutup)', a: 'shimete' },
    { q: 'Kanji ga ( ) kara, hiragana de kakimasu. (tidak mengerti)', a: 'wakaranai' },
    { q: 'Ame ga ( ) to, shiai ga dekimasen. (turun)', a: 'furu' },
  ] },
]

const ALL_TABS = [
  ...LESSONS.map(l => ({ key: `l${l.id}`, label: String(l.id), kind: 'lesson', ref: l })),
  ...REVIEWS.map(r => ({ key: r.id, label: r.label.replace('Rangkuman ', 'R.'), kind: 'review', ref: r })),
]

const activeKey = ref('l1')
const active = computed(() => ALL_TABS.find(x => x.key === activeKey.value))
const revealed = ref({})

function toggleReveal(sectionKey) {
  revealed.value[sectionKey] = !revealed.value[sectionKey]
}

function selectTab(key) {
  activeKey.value = key
  revealed.value = {}
}
</script>

<template>
  <div>
    <div class="mb-4">
      <h4 class="text-h4 mb-1">
        {{ t('mondaishuu.title') }}
      </h4>
      <p class="text-body-2 text-medium-emphasis mb-0">
        {{ t('mondaishuu.subtitle') }}
      </p>
    </div>

    <!-- Selector: pola pill yang sama dengan Kana/Kanji/Referensi Tata Bahasa/Kosakata -->
    <div
      class="mondaishuu-tabs mb-4"
      role="tablist"
      :aria-label="t('mondaishuu.title')"
    >
      <button
        v-for="tabItem in ALL_TABS"
        :key="tabItem.key"
        type="button"
        role="tab"
        class="mondaishuu-tabs__btn"
        :class="{
          'mondaishuu-tabs__btn--active': activeKey === tabItem.key,
          'mondaishuu-tabs__btn--review': tabItem.kind === 'review',
        }"
        :aria-selected="activeKey === tabItem.key"
        @click="selectTab(tabItem.key)"
      >
        {{ tabItem.label }}
      </button>
    </div>

    <template v-if="active">
      <VCard class="mb-4">
        <VCardItem>
          <VCardTitle>
            <template v-if="active.kind === 'lesson'">
              {{ t('mondaishuu.lesson_label', { n: active.ref.id }) }}
            </template>
            <template v-else>
              {{ active.ref.label }}
            </template>
          </VCardTitle>
          <VCardSubtitle v-if="active.kind === 'lesson'">
            {{ active.ref.focus }}
          </VCardSubtitle>
        </VCardItem>
      </VCard>

      <VCard
        v-for="(section, si) in active.ref.sections ?? [{ label: null, items: active.ref.items }]"
        :key="si"
        class="mb-4"
      >
        <VCardItem v-if="section.label">
          <VCardTitle class="text-body-1 font-weight-medium">
            {{ section.label }}
          </VCardTitle>
        </VCardItem>
        <VCardText>
          <ol class="mondaishuu-list">
            <li v-for="(item, ii) in section.items" :key="ii" class="mb-2">
              <span>{{ item.q }}</span>
              <span
                v-if="revealed[`${si}`]"
                class="mondaishuu-answer"
              >— {{ item.a }}</span>
            </li>
          </ol>
          <VBtn
            size="small"
            variant="tonal"
            :prepend-icon="revealed[`${si}`] ? 'tabler-eye-off' : 'tabler-eye'"
            @click="toggleReveal(`${si}`)"
          >
            {{ revealed[`${si}`] ? t('mondaishuu.hide_answers') : t('mondaishuu.show_answers') }}
          </VBtn>
        </VCardText>
      </VCard>
    </template>
  </div>
</template>

<style scoped>
/* ---------- selector — bahasa visual yang sama dengan halaman lain ---------- */
.mondaishuu-tabs {
  display: flex;
  flex-wrap: wrap;
  padding: 4px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 12px;
  background: rgb(var(--v-theme-surface));
  gap: 4px;
}

.mondaishuu-tabs__btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-inline-size: 40px;
  padding: 8px 12px;
  border: 0;
  border-radius: 9px;
  background: transparent;
  color: rgba(var(--v-theme-on-surface), 0.72);
  cursor: pointer;
  font-size: 0.875rem;
  font-weight: 500;
  white-space: nowrap;
  transition: background 0.15s ease, color 0.15s ease;
}

.mondaishuu-tabs__btn--review {
  font-size: 0.75rem;
}

.mondaishuu-tabs__btn:hover {
  background: rgba(var(--v-theme-primary), 0.08);
}

.mondaishuu-tabs__btn--active,
.mondaishuu-tabs__btn--active:hover {
  background: rgba(var(--v-theme-primary), 0.16);
  color: rgb(var(--v-theme-primary));
}

.mondaishuu-tabs__btn:focus-visible {
  outline: 2px solid rgb(var(--v-theme-primary));
  outline-offset: 2px;
}

.mondaishuu-list {
  padding-inline-start: 1.25rem;
}

.mondaishuu-answer {
  margin-inline-start: 6px;
  color: rgb(var(--v-theme-primary));
  font-weight: 500;
}
</style>
