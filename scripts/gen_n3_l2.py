#!/usr/bin/env python3
"""Pembuat berkas N3 Pelajaran 2 (chokai + kaiwa) dari penanda {漢字|かな}.
Jalankan dari root project:  python3 scripts/gen_n3_l2.py
"""
import json, re, os

ROOT = os.path.join(os.path.dirname(os.path.abspath(__file__)), '..')
M = re.compile(r'\{([^|{}]+)\|([^{}]+)\}')


def ja(t):    # tampilan: 漢字《かんじ》
    return M.sub(lambda m: f'{m.group(1)}《{m.group(2)}》', t)


def speak(t):  # TTS / target: kana saja
    return M.sub(lambda m: m.group(2), t)


def plain(t):  # kanji tanpa furigana
    return M.sub(lambda m: m.group(1), t)


def dump(path, data):
    with open(os.path.join(ROOT, path), 'w', encoding='utf-8') as f:
        json.dump(data, f, ensure_ascii=False, indent=4)
        f.write('\n')



F, Mx = 'female', 'male'
L = 'n3l2'
AUD = '/audio/chokai/lesson-n3-2'
OX = ['○', '×']


def q(i, turns, prompt, choices, answer, tr_id, tr_en):
    return {
        'id': f'{L}-q{i}',
        'audio_text': [{'speaker': s, 'text': t} for s, t in turns],
        'audio': f'{AUD}/{L}-q{i}.mp3',
        'prompt': ja(prompt),
        'choices': [ja(c) for c in choices],
        'answer': answer,
        'translate': {'id': tr_id, 'en': tr_en},
    }


questions = [
    q(1, [(F, '薬を 飲んだら、頭痛は どうなりましたか。'),
          (Mx, 'すぐに 楽に なりました。でも、会社に 着いて かばんを 開けたら、財布が なかったんです。'),
          (F, 'それは 大変でしたね。')],
      '男《おとこ》の 人《ひと》が かばんを 開《あ》けたら、どうでしたか。',
      ['{薬|くすり}が ありました', '{頭痛|ずつう}が なおりました', '{財布|さいふ}が ありませんでした'], 2,
      'Apa yang terjadi saat laki-laki itu membuka tas? — Dompetnya tidak ada.',
      'What happened when the man opened his bag? — His wallet was not there.'),
    q(2, [(Mx, '「定時」って 何ですか。'),
          (F, '会社が 決めた 終わりの 時間の ことですよ。たとえば 五時です。')],
      '「{定時|ていじ}」は 何《なん》ですか。',
      ['{長|なが}く {働|はたら}く {時間|じかん}', '{朝|あさ}の {時間|じかん}', '{会社|かいしゃ}が {決|き}めた {終|お}わりの {時間|じかん}'], 2,
      '"Teiji" itu apa? — Waktu berakhir yang ditetapkan perusahaan.',
      'What is "teiji"? — The closing time set by the company.'),
    q(3, [(F, '駅の 前に 新しい デパートが できるという 話を 聞きましたか。'),
          (Mx, 'ええ、来年 オープンするといううわさですね。'),
          (F, '楽しみですね。')],
      'デパートは いつ できますか。',
      ['{今年|ことし}', '{来年|らいねん}', '{来月|らいげつ}'], 1,
      'Kapan departemen store itu dibuka? — Tahun depan.',
      'When will the department store open? — Next year.'),
    q(4, [(Mx, '部長は 何と おっしゃっていましたか。'),
          (F, '明日までに 報告書を 出すように 言われました。')],
      '女《おんな》の 人《ひと》は 部長《ぶちょう》に 何《なに》を 言《い》われましたか。',
      ['{資料|しりょう}を {読|よ}むように', '{報告書|ほうこくしょ}を {出|だ}すように', '{早|はや}く {帰|かえ}るように'], 1,
      'Apa yang dikatakan kepala bagian kepada perempuan itu? — Agar menyerahkan laporan.',
      'What did the department head tell the woman? — To submit the report.'),
    q(5, [(Mx, 'このジュース、飲んでみて。'),
          (F, 'わあ、本物の 果物みたいな 味だね。'),
          (Mx, 'でしょう？水みたいに 軽くて、飲みやすいよ。')],
      'ジュースは どんな 味《あじ》ですか。',
      ['{甘|あま}すぎます', '{本物|ほんもの}の {果物|くだもの}みたいな {味|あじ}です', '{水|みず}みたいに {重|おも}いです'], 1,
      'Bagaimana rasa jusnya? — Seperti buah asli.',
      'How does the juice taste? — Like real fruit.'),
    q(6, [(F, 'お忙しいところ、すみません。少し よろしいでしょうか。')],
      '答《こた》えは どれですか。',
      ['いいえ、よろしいです。', 'はい、{忙|いそが}しいです。', 'はい、{何|なん}でしょうか。'], 2,
      'Maaf mengganggu, bolehkah sebentar? — Ya, ada apa?',
      'Sorry to trouble you, may I have a moment? — Yes, what is it?'),
    q(7, [(Mx, '部屋に 入ったら、友達が 寝て いたんです。')],
      '答《こた》えは どれですか。',
      ['そうですか。{寝|ね}て ください。', 'そうですか。{入|はい}って ください。', 'そうですか。びっくりしましたね。'], 2,
      'Saat masuk kamar, ternyata teman saya sedang tidur. — Begitu, pasti kaget, ya.',
      'When I entered the room, my friend was asleep. — I see, that must have been a surprise.'),
    q(8, [(F, 'ドアを 開けたら、荷物が 届いて いたんです。'),
          (Mx, 'よかったですね。')],
      '荷物《にもつ》は もう 届《とど》いて いました。', OX, 0,
      'Paket sudah sampai. → ○', 'The package had already arrived. → ○'),
    q(9, [(Mx, '先生に 教室で 食べないように 注意されました。'),
          (F, '大変でしたね。')],
      '先生《せんせい》は 教室《きょうしつ》で 食《た》べても いいと 言《い》いました。', OX, 1,
      'Guru bilang boleh makan di kelas. → ×', 'The teacher said it is fine to eat in the classroom. → ×'),
    q(10, [(Mx, '来週 台風が 来るというニュースを 見ました。'),
           (F, '本当ですか。じゃ、旅行は 中止ですね。')],
      '台風《たいふう》は もう 来《き》ました。', OX, 1,
      'Topan sudah datang. → ×', 'The typhoon has already come. → ×'),
]

kaiwa_turns = [
    (F, '山本さん、お忙しいところ、すみません。'),
    (Mx, 'はい、何ですか。'),
    (F, '「アポ」というのは どういう 意味ですか。'),
    (Mx, '人に 会う 約束の ことですよ。'),
    (F, 'ありがとうございます。部長に 明日 来るように 言われました。'),
    (Mx, 'じゃ、アポを 取りましょう。'),
]
kaiwa_qs = [
    {'id': f'{L}-kq1', 'prompt': ja('女《おんな》の 人《ひと》は 何《なに》を 聞《き》きましたか。'),
     'choices': [ja(c) for c in ['{部長|ぶちょう}の {名前|なまえ}', '「アポ」の {意味|いみ}', '{明日|あした}の {天気|てんき}']], 'answer': 1, 'audio': None,
     'translate': {'id': 'Apa yang ditanyakan perempuan itu? — Arti kata "apo".', 'en': 'What did the woman ask? — The meaning of "apo".'}},
    {'id': f'{L}-kq2', 'prompt': ja('「アポ」は どういう 意味《いみ》ですか。'),
     'choices': [ja(c) for c in ['{人|ひと}に {会|あ}う {約束|やくそく}', '{長|なが}い {時間|じかん}', '{会議室|かいぎしつ}']], 'answer': 0, 'audio': None,
     'translate': {'id': 'Apa arti "apo"? — Janji bertemu orang.', 'en': 'What does "apo" mean? — An appointment to meet someone.'}},
]

chokai = {
    'id': 'n3-2', 'level': 'N3',
    'focus': 'N3 Pelajaran 2 — Vたら、〜た, Nというのは〜ことだ, …という〜, 〜ように 言う／注意する／頼む, Nみたいだ／みたいな／みたいに, お忙しい ところ',
    'kaiwa': {'id': 'kaiwa',
              'audio_text': [{'speaker': s, 'text': t} for s, t in kaiwa_turns],
              'audio': '/audio/chokai/lesson-n3-2/kaiwa.mp3',
              'questions': kaiwa_qs},
    'questions': questions,
}
dump('public/data/chokai/lesson-n3-2.json', chokai)


def turn(sid, n, who, text, idt, ent):
    d = {'id': f'{sid}-t{n}', 'who': who, 'ja': ja(text), 'speak': speak(text),
         'id_text': idt, 'en_text': ent, 'audio': f'/audio/kaiwa/lesson-n3-2/{sid}-t{n}.mp3'}
    if who == 'you':
        acc = []
        for a in [plain(text), plain(text).replace(' ', ''), speak(text), speak(text).replace(' ', '')]:
            if a not in acc:
                acc.append(a)
        d['accept'] = acc
    return d


def scenario(k, title_id, title_en, setting_id, setting_en, goal_id, goal_en, patterns, partner, voice, rows):
    sid = f'{L}-s{k}'
    return {'id': sid, 'title_id': title_id, 'title_en': title_en,
            'setting_id': setting_id, 'setting_en': setting_en, 'goal_id': goal_id, 'goal_en': goal_en,
            'patterns': patterns, 'partner': {'name': partner, 'voice': voice}, 'you': {'name': 'ブディ'},
            'turns': [turn(sid, i, *r) for i, r in enumerate(rows, 1)]}


P, Y = 'partner', 'you'
scenarios = [
    scenario(1, 'Dompet tidak ada', 'Missing wallet',
             'Pagi di kantor. Kamu baru sampai dan menyadari sesuatu saat membuka tas.',
             'Morning at the office. You just arrived and noticed something when you opened your bag.',
             'Menceritakan penemuan dengan Vたら、〜た.', 'Report a discovery with Vtara ~ta.',
             ['Vたら、〜た'], '中村', 'female', [
        (P, 'ブディさん、{顔色|かおいろ}が {悪|わる}いですよ。どうしたんですか。', 'Budi, wajahmu pucat. Ada apa?', 'Budi, you look pale. What is wrong?'),
        (Y, 'かばんを {開|あ}けたら、{財布|さいふ}が なかったんです。', 'Waktu saya membuka tas, dompetnya tidak ada.', 'When I opened my bag, my wallet was not there.'),
        (P, 'えっ、{家|いえ}に {忘|わす}れたんじゃ ありませんか。', 'Eh, bukankah tertinggal di rumah?', 'Oh, did you not leave it at home?'),
        (Y, '{電話|でんわ}を かけたら、{家族|かぞく}が あると {言|い}いました。', 'Waktu saya menelepon, keluarga bilang ada.', 'When I called, my family said it was there.'),
        (P, 'よかったですね。', 'Syukurlah.', 'That is a relief.'),
        (Y, 'はい、{安心|あんしん}しました。', 'Ya, saya lega.', 'Yes, I feel relieved.')]),
    scenario(2, 'Arti istilah kantor', 'Meaning of office terms',
             'Di kantor. Kamu bertanya arti kata yang sering dipakai.',
             'At the office. You ask the meaning of a word used often.',
             'Menjelaskan istilah dengan Nというのは〜ことです.', 'Explain terms with N to iu no wa ~ koto desu.',
             ['Nというのは〜ことだ'], '佐藤', 'male', [
        (P, '{今日|きょう}は {残業|ざんぎょう}ですか。', 'Hari ini lembur?', 'Are you working overtime today?'),
        (Y, 'すみません、「{残業|ざんぎょう}」というのは どういう {意味|いみ}ですか。', 'Maaf, "zangyou" itu artinya apa?', 'Sorry, what does "zangyou" mean?'),
        (P, '{決|き}まった {時間|じかん}より {長|なが}く {働|はたら}く ことです。', 'Bekerja lebih lama dari waktu yang ditentukan.', 'It means working longer than the set hours.'),
        (Y, 'では、「{定時|ていじ}」というのは？', 'Lalu, "teiji" itu?', 'Then what is "teiji"?'),
        (P, '{会社|かいしゃ}が {決|き}めた {終|お}わりの {時間|じかん}の ことです。', 'Waktu berakhir yang ditetapkan perusahaan.', 'It is the closing time set by the company.'),
        (Y, 'わかりました。ありがとうございます。', 'Saya mengerti. Terima kasih.', 'I understand. Thank you.')]),
    scenario(3, 'Kabar tentang toko baru', 'News about a new shop',
             'Istirahat siang. Rekan kerja membicarakan kabar di sekitar stasiun.',
             'Lunch break. A colleague talks about news near the station.',
             'Menyebut isi kabar dengan 〜という 話／うわさ.', 'State the content of news with ~to iu hanashi / uwasa.',
             ['…という〜'], '山田', 'female', [
        (P, '{駅|えき}の {前|まえ}に {新|あたら}しい デパートが できるという {話|はなし}を {聞|き}きましたか。', 'Sudah dengar kabar akan ada departemen store baru di depan stasiun?', 'Have you heard that a new department store will open in front of the station?'),
        (Y, 'いいえ、{初|はじ}めて {聞|き}きました。', 'Belum, baru pertama kali dengar.', 'No, this is the first I have heard of it.'),
        (P, '{来年|らいねん}、オープンするといううわさですよ。', 'Katanya buka tahun depan.', 'The rumor is that it opens next year.'),
        (Y, '{本当|ほんとう}なら いいですね。', 'Kalau benar, bagus ya.', 'It would be nice if it is true.'),
        (P, '{来週|らいしゅう}、{台風|たいふう}が {来|く}るという ニュースも ありますよ。', 'Ada juga berita bahwa minggu depan topan datang.', 'There is also news that a typhoon is coming next week.'),
        (Y, 'そうですか。{気|き}を つけます。', 'Begitu. Saya akan berhati-hati.', 'I see. I will be careful.')]),
    scenario(4, 'Pesan dari kepala bagian', 'A message from the department head',
             'Sore di kantor. Seorang senior menanyakan apa kata kepala bagian.',
             'Afternoon at the office. A senior asks what the department head said.',
             'Menyampaikan perintah dengan 〜ように 言われました.', 'Relay instructions with ~you ni iwaremashita.',
             ['〜ように 言う／頼む'], '木村', 'male', [
        (P, '{部長|ぶちょう}は {何|なん}と おっしゃって いましたか。', 'Kepala bagian bilang apa?', 'What did the department head say?'),
        (Y, '{明日|あした}までに {報告書|ほうこくしょ}を {出|だ}すように {言|い}われました。', 'Saya disuruh menyerahkan laporan paling lambat besok.', 'I was told to submit the report by tomorrow.'),
        (P, '{他|ほか}には？', 'Ada lagi?', 'Anything else?'),
        (Y, '{資料|しりょう}は {机|つくえ}の {上|うえ}に {置|お}いて おくように {頼|たの}まれました。', 'Saya diminta menaruh materi di atas meja.', 'I was asked to leave the materials on the desk.'),
        (P, 'わかりました。{手伝|てつだ}いましょうか。', 'Baik. Mau saya bantu?', 'I see. Shall I help?'),
        (Y, 'はい、お{願|ねが}いします。', 'Ya, mohon bantuannya.', 'Yes, please.')]),
    scenario(5, 'Rasanya seperti buah asli', 'Tastes like real fruit',
             'Di dapur kantor. Rekan kerja menyuruhmu mencoba jus baru.',
             'In the office kitchen. A colleague has you try a new juice.',
             'Membuat perumpamaan lisan dengan みたいだ／みたいな／みたいに.', 'Compare casually with mitai da / na / ni.',
             ['Nみたいだ／みたいな／みたいに'], '高橋', 'female', [
        (P, 'この ジュース、{飲|の}んで みて。', 'Coba minum jus ini.', 'Try this juice.'),
        (Y, 'わあ、{本物|ほんもの}の {果物|くだもの}みたいな {味|あじ}ですね。', 'Wah, rasanya seperti buah asli.', 'Wow, it tastes like real fruit.'),
        (P, 'でしょう？{水|みず}みたいに {軽|かる}くて、{飲|の}みやすいよ。', 'Kan? Ringan seperti air, mudah diminum.', 'Right? It is light like water and easy to drink.'),
        (Y, 'もう {一杯|いっぱい} もらっても いいですか。', 'Boleh saya minta segelas lagi?', 'May I have another glass?'),
        (P, 'もちろん。たくさん あるよ。', 'Tentu. Masih banyak.', 'Of course. There is plenty.'),
        (Y, 'ありがとうございます。', 'Terima kasih.', 'Thank you.')]),
    scenario(6, 'Berkonsultasi dengan sopan', 'Asking for a consultation politely',
             'Sore hari. Kamu mau berkonsultasi dengan senior yang sedang sibuk.',
             'Afternoon. You want to consult a senior who looks busy.',
             'Membuka permintaan dengan お忙しい ところ、すみません.', 'Open a request with o-isogashii tokoro, sumimasen.',
             ['〜ところ'], '鈴木', 'male', [
        (Y, 'お{忙|いそが}しい ところ、すみません。{少|すこ}し よろしいでしょうか。', 'Maaf mengganggu kesibukan Anda. Bolehkah sebentar?', 'Sorry to trouble you when you are busy. May I have a moment?'),
        (P, 'はい、{何|なん}でしょうか。', 'Ya, ada apa?', 'Yes, what is it?'),
        (Y, '{来週|らいしゅう}の {資料|しりょう}の ことで {相談|そうだん}が あるのですが。', 'Saya ingin berkonsultasi tentang materi minggu depan.', 'I would like to consult you about next week\'s materials.'),
        (P, 'わかりました。{会議室|かいぎしつ}で {聞|き}きましょう。', 'Baik. Kita dengarkan di ruang rapat.', 'All right. Let us talk in the meeting room.'),
        (Y, 'ありがとうございます。お{願|ねが}いします。', 'Terima kasih. Mohon bantuannya.', 'Thank you. I appreciate it.'),
        (P, 'いいえ、いつでも どうぞ。', 'Tidak apa-apa, kapan saja silakan.', 'Not at all, anytime.')]),
]
kaiwa = {'id': 2, 'level': 'N3',
         'title_id': 'Pelajaran 2 (N3): Hasil, Istilah, dan Kabar',
         'title_en': 'N3 Lesson 2: Results, Terms and News',
         'scenarios': scenarios}
dump('public/data/kaiwa/lesson-n3-2.json', kaiwa)
dump('public/data/kaiwa/index-n3.json', {'lessons': [
    {'id': 1, 'file': 'lesson-n3-1.json', 'track': 'lesson', 'level': 'N3'},
    {'id': 2, 'file': 'lesson-n3-2.json', 'track': 'lesson', 'level': 'N3'}]})
print('ok', len(questions), len(scenarios))
