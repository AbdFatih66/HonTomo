#!/usr/bin/env python3
"""Pembuat berkas N3 Pelajaran 8 (chokai + kaiwa) dari penanda {漢字|かな}.
Jalankan dari root project:  python3 scripts/gen_n3_l8.py
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
L = 'n3l8'
AUD = '/audio/chokai/lesson-n3-8'
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
    q(1, [(Mx, '留守の あいだに 誰か 来ましたか。'),
          (F, 'ええ、宅配便が 届きましたよ。'),
          (Mx, '荷物は どこですか。'),
          (F, '玄関に 置いて あります。')],
      '荷物《にもつ》は どこに ありますか。',
      ['{台所|だいどころ}', '{玄関|げんかん}', '{会社|かいしゃ}'], 1,
      'Di mana barangnya? — Di pintu masuk.',
      'Where is the parcel? — At the entrance.'),
    q(2, [(F, '報告書は いつまでに 出せば いいですか。'),
          (Mx, '金曜日までに お願いします。'),
          (F, '今日は 何時まで 会社に いますか。'),
          (Mx, '夜まで いますから、いつでも どうぞ。')],
      '報告書《ほうこくしょ》は いつまでに 出《だ》しますか。',
      ['{今日|きょう}', '{来週|らいしゅう}', '{金曜日|きんようび}'], 2,
      'Kapan laporan harus diserahkan? — Paling lambat Jumat.',
      'By when should the report be submitted? — By Friday.'),
    q(3, [(Mx, '田中さんは どの 人ですか。'),
          (F, 'ほら、あの めがねを かけた 人ですよ。'),
          (Mx, '青い シャツを 着た 人ですか。'),
          (F, 'いいえ、その 隣の 人です。')],
      '田中《たなか》さんは どの 人《ひと》ですか。',
      ['{青|あお}い シャツを {着|き}た {人|ひと}', 'その {隣|となり}の めがねを かけた {人|ひと}', '{帽子|ぼうし}を かぶった {人|ひと}'], 1,
      'Tanaka yang mana? — Orang berkacamata di sebelahnya.',
      'Which one is Tanaka? — The person with glasses next to him.'),
    q(4, [(F, '日本では お正月に 何を 食べますか。'),
          (Mx, 'お雑煮を 食べますが、地方によって 作り方が 違います。'),
          (F, '家によっても 違いますか。'),
          (Mx, 'ええ、家によって 味も さまざまです。')],
      'お雑煮《ぞうに》の 作《つく》り方《かた》は どう 違《ちが》いますか。',
      ['ぜんぶ {同|おな}じです', '{年|とし}に よって {違|ちが}います', '{地方|ちほう}や {家|いえ}に よって {違|ちが}います'], 2,
      'Bagaimana perbedaan cara membuat ozouni? — Berbeda menurut daerah dan keluarga.',
      'How does the way of making ozouni differ? — By region and by family.'),
    q(5, [(F, 'あれ、エアコンが つけた ままだよ。'),
          (Mx, 'ごめん、つけた まま 出かけて しまった。'),
          (F, '電気代が もったいないよ。'),
          (Mx, '次から 気を つけるね。')],
      'エアコンは どう なって いましたか。',
      ['{消|け}して ありました', 'つけた ままでした', 'こわれて いました'], 1,
      'Bagaimana keadaan AC? — Dibiarkan menyala.',
      'What was the state of the air conditioner? — It was left on.'),
    q(6, [(F, 'どうして 日本語を 勉強して いるんですか。')],
      '答《こた》えは どれですか。',
      ['{日本語|にほんご}は {難|むずか}しいです。', '{毎日|まいにち} {勉強|べんきょう}します。', '{日本|にほん}で {働|はたら}きたいからです。'], 2,
      'Mengapa Anda belajar bahasa Jepang? — Karena ingin bekerja di Jepang.',
      'Why are you studying Japanese? — Because I want to work in Japan.'),
    q(7, [(Mx, '待ち合わせの 人、どんな 人ですか。')],
      '答《こた》えは どれですか。',
      ['{背|せ}が {高|たか}くて、{短|みじか}い {髪|かみ}を して います。', '{駅|えき}の {前|まえ}で {会|あ}います。', '{三時|さんじ}に {会|あ}います。'], 0,
      'Orang yang akan Anda temui seperti apa? — Tinggi, berambut pendek.',
      'What is the person you are meeting like? — Tall, with short hair.'),
    q(8, [(Mx, '待ち合わせの 人は 背が 高くて、短い 髪を して います。目は 大きくて、いつも 笑って います。'),
          (F, 'わかりました。探して みます。')],
      '待《ま》ち合《あ》わせの 人《ひと》は 髪《かみ》が 長《なが》いです。', OX, 1,
      'Orang yang ditemui berambut panjang. → ×',
      'The person being met has long hair. → ×'),
    q(9, [(F, '日本では 地方によって お雑煮の 作り方が 違います。'),
          (Mx, 'そうですか。')],
      'お雑煮《ぞうに》の 作《つく》り方《かた》は 地方《ちほう》によって 違《ちが》います。', OX, 0,
      'Cara membuat ozouni berbeda menurut daerah. → ○',
      'The way ozouni is made differs by region. → ○'),
    q(10, [(Mx, '今日は 夜まで 会社に いますから、いつでも どうぞ。'),
           (F, 'ありがとうございます。')],
      '男《おとこ》の 人《ひと》は 夕方《ゆうがた》 帰《かえ》ります。', OX, 1,
      'Laki-laki itu pulang sore hari. → ×',
      'The man goes home in the evening. → ×'),
]

kaiwa_turns = [
    (F, 'すみません、迷子の お知らせを お願いします。'),
    (Mx, 'はい。お子さんは どんな 子ですか。'),
    (F, '五歳の 男の子で、赤い シャツを 着て いて、リュックを 背負って います。'),
    (Mx, '髪は？'),
    (F, '短い 髪を して います。'),
    (Mx, 'わかりました。すぐ 捜します。'),
]
kaiwa_qs = [
    {'id': f'{L}-kq1', 'prompt': ja('迷子《まいご》は どんな 子《こ》ですか。'),
     'choices': [ja(c) for c in ['{十歳|じゅっさい}の {女|おんな}の {子|こ}', '{五歳|ごさい}の {男|おとこ}の {子|こ}', '{三歳|さんさい}の {男|おとこ}の {子|こ}']], 'answer': 1, 'audio': None,
     'translate': {'id': 'Anak yang hilang seperti apa? — Anak laki-laki lima tahun.', 'en': 'What kind of child is lost? — A five-year-old boy.'}},
    {'id': f'{L}-kq2', 'prompt': ja('その 子《こ》は 何《なに》を 背負《せお》って いますか。'),
     'choices': [ja(c) for c in ['リュック', 'かばん', '{帽子|ぼうし}']], 'answer': 0, 'audio': None,
     'translate': {'id': 'Apa yang dipanggul anak itu? — Ransel.', 'en': 'What is the child carrying on his back? — A backpack.'}},
]

chokai = {
    'id': 'n3-8', 'level': 'N3',
    'focus': 'N3 Pelajaran 8 — Vている／Nの あいだ(に), Nまで／Vるまで と Nまでに／Vるまでに, Vた N, Nによって, Vたまま／Nのまま, …からだ, 髪／目／形を している',
    'kaiwa': {'id': 'kaiwa',
              'audio_text': [{'speaker': s, 'text': t} for s, t in kaiwa_turns],
              'audio': '/audio/chokai/lesson-n3-8/kaiwa.mp3',
              'questions': kaiwa_qs},
    'questions': questions,
}
dump('public/data/chokai/lesson-n3-8.json', chokai)


def turn(sid, n, who, text, idt, ent):
    d = {'id': f'{sid}-t{n}', 'who': who, 'ja': ja(text), 'speak': speak(text),
         'id_text': idt, 'en_text': ent, 'audio': f'/audio/kaiwa/lesson-n3-8/{sid}-t{n}.mp3'}
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
    scenario(1, 'Paket datang saat tidak di rumah', 'A parcel while you were away',
             'Sore di rumah. Kamu bertanya kepada anggota rumah tentang paket.',
             'Evening at home. You ask a housemate about a parcel.',
             'Menyatakan kejadian selama suatu periode dengan あいだ(に).', 'Describe events during a period with aida (ni).',
             ['Vている／Nの あいだ(に)'], '中村', 'female', [
        (Y, '{留守|るす}の あいだに、{誰|だれ}か {来|き}ましたか。', 'Selama saya tidak di rumah, ada yang datang?', 'Did anyone come while I was out?'),
        (P, 'ええ、{宅配便|たくはいびん}が {届|とど}きましたよ。', 'Ya, paket kurir datang.', 'Yes, a parcel was delivered.'),
        (Y, '{荷物|にもつ}は どこですか。', 'Barangnya di mana?', 'Where is it?'),
        (P, '{玄関|げんかん}に {置|お}いて あります。', 'Ditaruh di pintu masuk.', 'I left it at the entrance.'),
        (Y, 'わかりました。{子|こ}どもが {寝|ね}て いる あいだに {取|と}りに {行|い}きます。', 'Baik. Saya ambil selagi anak tidur.', 'Understood. I will get it while the child is sleeping.'),
        (P, 'はい、お{願|ねが}いします。', 'Ya, tolong ya.', 'Yes, please.')]),
    scenario(2, 'Tenggat laporan', 'Report deadline',
             'Di kantor. Kamu menanyakan tenggat dan jam kerja atasanmu.',
             'At the office. You ask about the deadline and your boss\'s hours.',
             'Membedakan まで dan までに.', 'Distinguish made and made ni.',
             ['Nまで／Vるまで', 'Nまでに／Vるまでに'], '佐藤', 'male', [
        (Y, '{報告書|ほうこくしょ}は いつまでに {出|だ}せば いいですか。', 'Laporan harus diserahkan paling lambat kapan?', 'By when should I submit the report?'),
        (P, '{金曜日|きんようび}までに お{願|ねが}いします。', 'Paling lambat Jumat, ya.', 'By Friday, please.'),
        (Y, '{今日|きょう}は {何時|なんじ}まで {会社|かいしゃ}に いますか。', 'Hari ini Anda di kantor sampai jam berapa?', 'Until what time are you at the office today?'),
        (P, '{夜|よる}まで いますから、いつでも どうぞ。', 'Saya sampai malam, jadi kapan saja silakan.', 'I will be here until night, so come any time.'),
        (Y, 'では、{金曜日|きんようび}までに {必|かなら}ず {出|だ}します。', 'Kalau begitu, pasti saya serahkan paling lambat Jumat.', 'Then I will definitely submit it by Friday.'),
        (P, 'お{願|ねが}いしますね。', 'Saya mengandalkanmu.', 'I am counting on you.')]),
    scenario(3, 'Yang mana Tanaka?', 'Which one is Tanaka?',
             'Di acara kantor. Kamu mencari Tanaka di antara banyak orang.',
             'At a company event. You look for Tanaka among many people.',
             'Menunjuk orang dengan kata kerja bentuk た + N.', 'Pick out people with a V-ta + noun phrase.',
             ['Vた N'], '山田', 'female', [
        (Y, '{田中|たなか}さんは どの {人|ひと}ですか。', 'Tanaka yang mana?', 'Which one is Tanaka?'),
        (P, 'ほら、あの めがねを かけた {人|ひと}ですよ。', 'Itu, yang berkacamata.', 'Look, the one wearing glasses.'),
        (Y, '{青|あお}い シャツを {着|き}た {人|ひと}ですか。', 'Yang memakai kemeja biru?', 'The one wearing a blue shirt?'),
        (P, 'いいえ、その {隣|となり}の {帽子|ぼうし}を かぶった {人|ひと}です。', 'Bukan, orang di sebelahnya yang memakai topi.', 'No, the one next to him wearing a hat.'),
        (Y, 'ああ、あの {人|ひと}ですね。ありがとうございます。', 'Oh, orang itu. Terima kasih.', 'Oh, that person. Thank you.'),
        (P, 'いいえ。', 'Sama-sama.', 'You are welcome.')]),
    scenario(4, 'Ozouni berbeda tiap daerah', 'Ozouni differs by region',
             'Awal Januari di kantor. Kamu bertanya soal makanan Tahun Baru.',
             'Early January at the office. You ask about New Year food.',
             'Menyatakan perbedaan dengan Nによって.', 'State differences with N ni yotte.',
             ['Nによって'], '木村', 'male', [
        (Y, '{日本|にほん}では お{正月|しょうがつ}に {何|なに}を {食|た}べますか。', 'Di Jepang, apa yang dimakan saat Tahun Baru?', 'What do people eat at New Year in Japan?'),
        (P, 'お{雑煮|ぞうに}を {食|た}べますが、{地方|ちほう}によって {作|つく}り{方|かた}が {違|ちが}います。', 'Ozouni, tetapi cara membuatnya beda tiap daerah.', 'Ozouni, but the way it is made differs by region.'),
        (Y, '{家|いえ}によっても {違|ちが}いますか。', 'Apakah beda juga tiap keluarga?', 'Does it differ by family too?'),
        (P, 'ええ、{家|いえ}によって {味|あじ}も さまざまです。', 'Ya, rasanya juga beragam menurut keluarga.', 'Yes, the taste varies by family too.'),
        (Y, '{人|ひと}によって {好|す}きな {味|あじ}も {違|ちが}いますね。', 'Selera orang juga berbeda-beda ya.', 'Preferred tastes differ from person to person too.'),
        (P, 'そうですね。おもしろいでしょう。', 'Betul. Menarik, kan?', 'That is right. Is it not interesting?')]),
    scenario(5, 'AC masih menyala dan alasan belajar', 'AC left on and why you study',
             'Di rumah teman. Temanmu menemukan AC menyala, lalu bertanya alasan belajarmu.',
             'At a friend\'s house. She finds the AC on, then asks why you study Japanese.',
             'Memakai Vたまま dan alasan …からだ.', 'Use V-ta mama and the reason pattern ...kara da.',
             ['Vたまま', '…からだ'], '高橋', 'female', [
        (P, 'あれ、エアコンが つけた ままだよ。', 'Lho, AC-nya masih menyala.', 'Hey, the air conditioner is still on.'),
        (Y, 'ごめん、つけた まま {出|で}かけて しまった。', 'Maaf, aku pergi tanpa mematikannya.', 'Sorry, I went out leaving it on.'),
        (P, '{電気代|でんきだい}が もったいないよ。', 'Sayang biaya listriknya.', 'It is a waste of electricity.'),
        (Y, '{次|つぎ}から {気|き}を つけるね。', 'Mulai lain kali aku hati-hati.', 'I will be careful from next time.'),
        (P, 'ところで、どうして {日本語|にほんご}を {勉強|べんきょう}して いるの？', 'Omong-omong, kenapa kamu belajar bahasa Jepang?', 'By the way, why are you studying Japanese?'),
        (Y, '{日本|にほん}で {働|はたら}きたいからだよ。', 'Karena ingin bekerja di Jepang.', 'Because I want to work in Japan.')]),
    scenario(6, 'Ciri orang yang dijemput', 'Describing the person you are meeting',
             'Di bandara. Petugas menanyakan ciri orang yang akan kamu jemput.',
             'At the airport. A staff member asks what the person you are meeting looks like.',
             'Menjelaskan penampilan dengan 髪／目を している.', 'Describe looks with kami / me o shite iru.',
             ['髪／目／形を している'], '鈴木', 'male', [
        (P, '{待|ま}ち{合|あ}わせの {人|ひと}は どんな {人|ひと}ですか。', 'Orang yang dijemput seperti apa?', 'What is the person you are meeting like?'),
        (Y, '{背|せ}が {高|たか}くて、{短|みじか}い {髪|かみ}を して います。', 'Tinggi dan berambut pendek.', 'Tall, with short hair.'),
        (P, '{目|め}は？', 'Matanya?', 'And the eyes?'),
        (Y, '{大|おお}きな {目|め}を して いて、いつも {笑|わら}って います。', 'Bermata besar dan selalu tersenyum.', 'Big eyes, and always smiling.'),
        (P, '{持|も}ち{物|もの}は？', 'Barang bawaannya?', 'And what is he carrying?'),
        (Y, '{青|あお}い リュックを {背負|せお}って います。', 'Memanggul ransel biru.', 'He is carrying a blue backpack.')]),
]
kaiwa = {'id': 8, 'level': 'N3',
         'title_id': 'Pelajaran 8 (N3): Ciri, Tenggat, dan Keadaan',
         'title_en': 'N3 Lesson 8: Features, Deadlines and States',
         'scenarios': scenarios}
dump('public/data/kaiwa/lesson-n3-8.json', kaiwa)
dump('public/data/kaiwa/index-n3.json', {'lessons': [
    {'id': n, 'file': f'lesson-n3-{n}.json', 'track': 'lesson', 'level': 'N3'} for n in range(1, 9)]})
print('ok', len(questions), len(scenarios))
