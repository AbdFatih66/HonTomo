#!/usr/bin/env python3
"""Pembuat berkas N3 Pelajaran 9 (chokai + kaiwa) dari penanda {漢字|かな}.
Jalankan dari root project:  python3 scripts/gen_n3_l9.py
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
L = 'n3l9'
AUD = '/audio/chokai/lesson-n3-9'
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
    q(1, [(F, 'いらっしゃいませ。何を お探しですか。'),
          (Mx, '母への 誕生日の プレゼントです。'),
          (F, 'お母さまは 青い かばんを お持ちですか。'),
          (Mx, 'いいえ、持って いません。')],
      '男《おとこ》の 人《ひと》は 何《なに》を 探《さが》して いますか。',
      ['{自分|じぶん}の かばん', '{母|はは}への {誕生日|たんじょうび}の プレゼント', '{父|ちち}への {本|ほん}'], 1,
      'Apa yang dicari laki-laki itu? — Hadiah ulang tahun untuk ibu.',
      'What is the man looking for? — A birthday present for his mother.'),
    q(2, [(Mx, 'すみません、ここに 座っても かまいませんか。'),
          (F, 'ええ、どうぞ。'),
          (Mx, '荷物を 隣に 置いても かまいませんか。'),
          (F, 'ええ、大丈夫ですよ。')],
      '男《おとこ》の 人《ひと》は 何《なに》を しますか。',
      ['{席|せき}を {立|た}ちます', '{荷物|にもつ}を {持|も}って {帰|かえ}ります', '{座|すわ}って、{荷物|にもつ}を {隣|となり}に {置|お}きます'], 2,
      'Apa yang dilakukan laki-laki itu? — Duduk dan menaruh barang di sebelah.',
      'What does the man do? — Sits down and puts his luggage next to him.'),
    q(3, [(Mx, '今日は 昨日より 暑いですね。'),
          (F, 'そうですか。昨日ほど 暑くは ないと 思いますよ。'),
          (Mx, 'そうですか？'),
          (F, 'ええ、昨日は 本当に 暑かったですから。')],
      '女《おんな》の 人《ひと》は どちらが 暑《あつ》いと 思《おも》って いますか。',
      ['{今日|きょう}', '{昨日|きのう}', '{同|おな}じ'], 1,
      'Menurut perempuan itu, mana yang lebih panas? — Kemarin.',
      'Which does the woman think was hotter? — Yesterday.'),
    q(4, [(F, '日本で 一番 好きな 場所は どこですか。'),
          (Mx, '富士山ほど 美しい 山は ないと 思います。'),
          (F, '登った ことが ありますか。'),
          (Mx, 'いいえ。でも、いつか 登って みたいです。')],
      '男《おとこ》の 人《ひと》は 富士山《ふじさん》に 登《のぼ》った ことが ありますか。',
      ['あります', '{去年|きょねん} {登|のぼ}りました', 'ありません'], 2,
      'Apakah laki-laki itu pernah mendaki Fuji? — Belum.',
      'Has the man climbed Mt. Fuji? — No.'),
    q(5, [(Mx, '昨日の 工場は どうでしたか。'),
          (F, '機械が 故障した ために、午後は 作業が 止まりました。'),
          (Mx, '原因は 何でしたか。'),
          (F, '部品が 古く なって いた ためです。')],
      '機械《きかい》が 故障《こしょう》した 原因《げんいん》は 何《なん》ですか。',
      ['{停電|ていでん}', '{部品|ぶひん}が {古|ふる}く なって いた こと', '{大雪|おおゆき}'], 1,
      'Apa penyebab mesin rusak? — Komponennya sudah tua.',
      'What caused the machine to break down? — The parts had grown old.'),
    q(6, [(Mx, '昨日の パーティー、来なかったね。')],
      '答《こた》えは どれですか。',
      ['{仕事|しごと}が {終|お}わらなかったんだ。', 'パーティーは {楽|たの}しかった。', '{行|い}きました。'], 0,
      'Kamu tidak datang ke pesta kemarin. — Pekerjaanku belum selesai.',
      'You did not come to the party yesterday. — My work did not get finished.'),
    q(7, [(Mx, '荷物を 隣に 置いても かまいませんか。')],
      '答《こた》えは どれですか。',
      ['いいえ、{置|お}きました。', 'ええ、{大丈夫|だいじょうぶ}ですよ。', '{荷物|にもつ}は {重|おも}いです。'], 1,
      'Bolehkah menaruh barang di sebelah? — Ya, tidak apa-apa.',
      'May I put my luggage next to me? — Yes, that is fine.'),
    q(8, [(Mx, '早く 終わって いれば、行けたのに。'),
          (F, 'みんな 楽しんで いたよ。')],
      '男《おとこ》の 人《ひと》は パーティーに 行《い》きました。', OX, 1,
      'Laki-laki itu pergi ke pesta. → ×',
      'The man went to the party. → ×'),
    q(9, [(F, 'この 町は 東京ほど にぎやかでは ありません。'),
          (Mx, 'そうですね。')],
      'この 町《まち》は 東京《とうきょう》ほど にぎやかでは ありません。', OX, 0,
      'Kota ini tidak seramai Tokyo. → ○',
      'This town is not as lively as Tokyo. → ○'),
    q(10, [(Mx, '大雪の ために、電車が 遅れました。'),
           (F, '大変でしたね。')],
      '電車《でんしゃ》は 雨《あめ》の ために 遅《おく》れました。', OX, 1,
      'Kereta terlambat karena hujan. → ×',
      'The train was late because of rain. → ×'),
]

kaiwa_turns = [
    (F, 'いらっしゃいませ。何を お探しですか。'),
    (Mx, '辞書を 探して います。例文が たくさん 載って いる ものが いいんですが。'),
    (F, 'それでしたら、こちらの 国語辞書が よろしいんじゃないでしょうか。'),
    (Mx, '和英辞書は ありませんか。'),
    (F, 'こちらです。検索機能も ついて います。'),
    (Mx, 'じゃあ、それを ください。'),
]
kaiwa_qs = [
    {'id': f'{L}-kq1', 'prompt': ja('男《おとこ》の 人《ひと》は どんな 辞書《じしょ》が 欲《ほ》しいですか。'),
     'choices': [ja(c) for c in ['{小|ちい}さい {辞書|じしょ}', '{安|やす}い {辞書|じしょ}', '{例文|れいぶん}が たくさん {載|の}って いる {辞書|じしょ}']], 'answer': 2, 'audio': None,
     'translate': {'id': 'Kamus seperti apa yang diinginkan? — Yang memuat banyak contoh kalimat.', 'en': 'What kind of dictionary does he want? — One with many example sentences.'}},
    {'id': f'{L}-kq2', 'prompt': ja('男《おとこ》の 人《ひと》は 最後《さいご》に 何《なに》を 買《か》いますか。'),
     'choices': [ja(c) for c in ['{国語辞書|こくごじしょ}', '{和英辞書|わえいじしょ}', '{電子辞書|でんしじしょ}']], 'answer': 1, 'audio': None,
     'translate': {'id': 'Akhirnya apa yang dibeli? — Kamus Jepang-Inggris.', 'en': 'What does he buy in the end? — The Japanese-English dictionary.'}},
]

chokai = {
    'id': 'n3-9', 'level': 'N3',
    'focus': 'N3 Pelajaran 9 — お〜です, Vても かまわない, AはBほど〜ない, Nほど〜は ない, …ため(に)／…ためだ, Vたら／Vば、…た(のに)',
    'kaiwa': {'id': 'kaiwa',
              'audio_text': [{'speaker': s, 'text': t} for s, t in kaiwa_turns],
              'audio': '/audio/chokai/lesson-n3-9/kaiwa.mp3',
              'questions': kaiwa_qs},
    'questions': questions,
}
dump('public/data/chokai/lesson-n3-9.json', chokai)


def turn(sid, n, who, text, idt, ent):
    d = {'id': f'{sid}-t{n}', 'who': who, 'ja': ja(text), 'speak': speak(text),
         'id_text': idt, 'en_text': ent, 'audio': f'/audio/kaiwa/lesson-n3-9/{sid}-t{n}.mp3'}
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
    scenario(1, 'Mencari hadiah untuk ibu', 'Looking for a gift for Mom',
             'Di toko tas. Pegawai menyapamu dengan bahasa sopan.',
             'In a bag shop. The clerk greets you in polite Japanese.',
             'Menanggapi bahasa hormat お〜です.', 'Respond to honorific o~ desu.',
             ['お〜です'], '中村', 'female', [
        (P, 'いらっしゃいませ。{何|なに}を お{探|さが}しですか。', 'Selamat datang. Anda mencari apa?', 'Welcome. What are you looking for?'),
        (Y, '{母|はは}への {誕生日|たんじょうび}の プレゼントです。', 'Hadiah ulang tahun untuk ibu saya.', 'A birthday present for my mother.'),
        (P, 'お{母|かあ}さまは {青|あお}い かばんを お{持|も}ちですか。', 'Apakah ibu Anda sudah punya tas biru?', 'Does your mother already have a blue bag?'),
        (Y, 'いいえ、{持|も}って いません。', 'Tidak, belum punya.', 'No, she does not.'),
        (P, 'それでしたら、こちらは いかがでしょうか。', 'Kalau begitu, bagaimana yang ini?', 'In that case, how about this one?'),
        (Y, 'いいですね。それを ください。', 'Bagus. Saya ambil yang itu.', 'Nice. I will take that one.')]),
    scenario(2, 'Meminta izin di kereta', 'Asking permission on the train',
             'Di kereta. Kamu meminta izin kepada penumpang sebelahmu.',
             'On a train. You ask the passenger next to you for permission.',
             'Meminta izin dengan Vても かまいませんか.', 'Ask permission with V-te mo kamaimasen ka.',
             ['Vても かまわない'], '佐藤', 'male', [
        (Y, 'すみません、ここに {座|すわ}っても かまいませんか。', 'Maaf, bolehkah saya duduk di sini?', 'Excuse me, may I sit here?'),
        (P, 'ええ、どうぞ。', 'Ya, silakan.', 'Yes, please.'),
        (Y, '{荷物|にもつ}を {隣|となり}に {置|お}いても かまいませんか。', 'Bolehkah saya menaruh barang di sebelah?', 'May I put my luggage next to me?'),
        (P, 'ええ、{大丈夫|だいじょうぶ}ですよ。', 'Ya, tidak apa-apa.', 'Yes, that is fine.'),
        (Y, 'ありがとうございます。{窓|まど}を {開|あ}けても かまいませんか。', 'Terima kasih. Bolehkah saya membuka jendela?', 'Thank you. May I open the window?'),
        (P, 'はい、かまいませんよ。', 'Ya, silakan.', 'Yes, go ahead.')]),
    scenario(3, 'Panas hari ini dan kemarin', 'Today\'s heat versus yesterday\'s',
             'Pagi hari. Rekan kerja membicarakan cuaca.',
             'Morning. A colleague talks about the weather.',
             'Membandingkan dengan Bほど〜ない.', 'Compare with B hodo ~ nai.',
             ['AはBほど〜ない'], '山田', 'female', [
        (P, '{今日|きょう}は {昨日|きのう}より {暑|あつ}いですね。', 'Hari ini lebih panas dari kemarin ya.', 'It is hotter today than yesterday, is it not?'),
        (Y, 'そうですか。{昨日|きのう}ほど {暑|あつ}くは ないと {思|おも}いますよ。', 'Begitu? Saya kira tidak sepanas kemarin.', 'Is it? I think it is not as hot as yesterday.'),
        (P, 'そうですか？', 'Benarkah?', 'Really?'),
        (Y, 'ええ、{昨日|きのう}は {本当|ほんとう}に {暑|あつ}かったですから。', 'Ya, kemarin benar-benar panas.', 'Yes, yesterday was really hot.'),
        (P, '{確|たし}かに。{今日|きょう}は {風|かぜ}が ありますね。', 'Benar juga. Hari ini ada angin.', 'True. There is a breeze today.'),
        (Y, 'ええ、{気温|きおん}は {昨日|きのう}ほど {高|たか}くないです。', 'Ya, suhunya tidak setinggi kemarin.', 'Yes, the temperature is not as high as yesterday.')]),
    scenario(4, 'Gunung paling indah', 'The most beautiful mountain',
             'Istirahat siang. Rekan kerja bertanya tempat favoritmu di Jepang.',
             'Lunch break. A colleague asks your favorite place in Japan.',
             'Menyatakan yang terbaik dengan Nほど〜はない.', 'State the best with N hodo ~ wa nai.',
             ['Nほど〜は ない'], '木村', 'male', [
        (P, '{日本|にほん}で {一番|いちばん} {好|す}きな {場所|ばしょ}は どこですか。', 'Tempat favoritmu di Jepang di mana?', 'What is your favorite place in Japan?'),
        (Y, '{富士山|ふじさん}ほど {美|うつく}しい {山|やま}は ないと {思|おも}います。', 'Saya kira tidak ada gunung seindah Fuji.', 'I think there is no mountain as beautiful as Mt. Fuji.'),
        (P, '{登|のぼ}った ことが ありますか。', 'Pernah mendaki?', 'Have you climbed it?'),
        (Y, 'いいえ。でも、いつか {登|のぼ}って みたいです。', 'Belum. Tapi suatu hari ingin mencoba.', 'No. But I would like to climb it someday.'),
        (P, '{夏|なつ}に {登|のぼ}る {人|ひと}が {多|おお}いですよ。', 'Banyak yang mendaki saat musim panas.', 'Many people climb in summer.'),
        (Y, '{夏|なつ}ほど {登|のぼ}りやすい {季節|きせつ}は ないでしょうね。', 'Tidak ada musim yang lebih mudah untuk mendaki daripada musim panas, ya.', 'There is probably no season easier for climbing than summer.')]),
    scenario(5, 'Laporan penyebab kerusakan mesin', 'Report on a machine breakdown',
             'Pagi di pabrik. Atasan menanyakan kejadian kemarin.',
             'Morning at the factory. A boss asks about yesterday\'s incident.',
             'Menjelaskan sebab dengan ため(に)／ためだ.', 'Explain causes with tame (ni) / tame da.',
             ['…ため(に)', '…ためだ'], '高橋', 'female', [
        (P, '{昨日|きのう}の {工場|こうじょう}は どうでしたか。', 'Bagaimana pabrik kemarin?', 'How was the factory yesterday?'),
        (Y, '{機械|きかい}が {故障|こしょう}した ために、{午後|ごご}は {作業|さぎょう}が {止|と}まりました。', 'Karena mesin rusak, pekerjaan sore berhenti.', 'The machine broke down, so work stopped in the afternoon.'),
        (P, '{原因|げんいん}は {何|なん}でしたか。', 'Penyebabnya apa?', 'What was the cause?'),
        (Y, '{部品|ぶひん}が {古|ふる}く なって いた ためです。', 'Karena komponennya sudah tua.', 'It was because the parts had grown old.'),
        (P, 'そうですか。{今|いま}は {直|なお}りましたか。', 'Begitu. Sekarang sudah diperbaiki?', 'I see. Is it fixed now?'),
        (Y, 'はい、{新|あたら}しい {部品|ぶひん}に {取|と}り{替|か}えた ために、{今|いま}は {動|うご}いて います。', 'Ya, karena komponen diganti baru, sekarang berjalan.', 'Yes, since we replaced the parts with new ones, it is running now.')]),
    scenario(6, 'Seandainya saya pergi', 'If only I had gone',
             'Pagi di kantor. Teman menanyakan kenapa kamu tidak datang ke pesta.',
             'Morning at the office. A friend asks why you missed the party.',
             'Menyatakan penyesalan dengan Vば／Vたら…のに.', 'Express regret with V-ba / V-tara ... noni.',
             ['Vば／Vたら、…た(のに)'], '鈴木', 'male', [
        (P, '{昨日|きのう}の パーティー、{来|こ}なかったね。', 'Kamu tidak datang ke pesta kemarin.', 'You did not come to yesterday\'s party.'),
        (Y, '{仕事|しごと}が {終|お}わらなかったんだ。{早|はや}く {終|お}わって いれば、{行|い}けたのに。', 'Pekerjaanku belum selesai. Kalau selesai lebih awal, aku bisa datang.', 'My work did not get done. If I had finished early, I could have gone.'),
        (P, 'みんな {楽|たの}しんで いたよ。', 'Semua bersenang-senang.', 'Everyone was having fun.'),
        (Y, '{行|い}けば よかったなあ。', 'Andai saja aku pergi.', 'I wish I had gone.'),
        (P, '{来月|らいげつ}も あるよ。', 'Bulan depan ada lagi.', 'There is another next month.'),
        (Y, 'じゃあ、{今度|こんど}は {必|かなら}ず {行|い}く。', 'Kalau begitu, lain kali aku pasti datang.', 'Then I will definitely go next time.')]),
]
kaiwa = {'id': 9, 'level': 'N3',
         'title_id': 'Pelajaran 9 (N3): Berbelanja, Perbandingan, dan Sebab',
         'title_en': 'N3 Lesson 9: Shopping, Comparison and Causes',
         'scenarios': scenarios}
dump('public/data/kaiwa/lesson-n3-9.json', kaiwa)
dump('public/data/kaiwa/index-n3.json', {'lessons': [
    {'id': n, 'file': f'lesson-n3-{n}.json', 'track': 'lesson', 'level': 'N3'} for n in range(1, 10)]})
print('ok', len(questions), len(scenarios))
