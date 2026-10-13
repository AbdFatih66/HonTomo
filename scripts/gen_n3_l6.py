#!/usr/bin/env python3
"""Pembuat berkas N3 Pelajaran 6 (chokai + kaiwa) dari penanda {漢字|かな}.
Jalankan dari root project:  python3 scripts/gen_n3_l6.py
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
L = 'n3l6'
AUD = '/audio/chokai/lesson-n3-6'
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
    q(1, [(F, '田中さんは 何て 言ってた？'),
          (Mx, '今日は 早く 帰るって。'),
          (F, '理由は？'),
          (Mx, '子どもが 熱を 出したって 言っていたよ。')],
      '田中《たなか》さんは どうして 早《はや》く 帰《かえ》りますか。',
      ['{旅行|りょこう}に {行|い}くからです', '{子|こ}どもが {熱|ねつ}を {出|だ}したからです', '{会議|かいぎ}が {終|お}わったからです'], 1,
      'Mengapa Tanaka pulang cepat? — Karena anaknya demam.',
      'Why is Tanaka going home early? — His child has a fever.'),
    q(2, [(F, '札幌って、どんな 町？'),
          (Mx, '冬は 雪が 多くて、食べ物が おいしい 町だよ。'),
          (F, '行った ことが あるの？'),
          (Mx, 'うん、去年 行った。')],
      '男《おとこ》の 人《ひと》は 札幌《さっぽろ》に 行《い》った ことが ありますか。',
      ['{去年|きょねん} {行|い}きました', '{行|い}った ことが ありません', '{来年|らいねん} {行|い}きます'], 0,
      'Apakah laki-laki itu pernah ke Sapporo? — Pernah, tahun lalu.',
      'Has the man been to Sapporo? — Yes, last year.'),
    q(3, [(Mx, '来月の 同窓会、行く？'),
          (F, 'ううん、行く つもりは ないよ。'),
          (Mx, 'どうして？'),
          (F, '仕事が 忙しいし、あまり 気が 進まないんだ。')],
      '女《おんな》の 人《ひと》は 同窓会《どうそうかい》に 行《い》きますか。',
      ['{行|い}きます', 'まだ わかりません', '{行|い}きません'], 2,
      'Apakah perempuan itu pergi ke reuni? — Tidak.',
      'Is the woman going to the reunion? — No.'),
    q(4, [(F, '昨日の 会議、なぜ 来なかったの？'),
          (Mx, 'すみません、行く つもりだったんですが、お客さんが 来て しまって。'),
          (F, '連絡して ほしかったな。')],
      '男《おとこ》の 人《ひと》は どうして 会議《かいぎ》に 行《い》きませんでしたか。',
      ['お{客|きゃく}さんが {来|き}たからです', '{病気|びょうき}だったからです', '{忘|わす}れたからです'], 0,
      'Mengapa laki-laki itu tidak ke rapat? — Karena ada tamu datang.',
      'Why did the man not go to the meeting? — A customer came.'),
    q(5, [(F, '最近、うちの 子は スマホばかり 見て いるんです。'),
          (Mx, '宿題は して いますか。'),
          (F, 'それも 忘れて、遊んで ばかり いて…。')],
      '子《こ》どもは 何《なに》を して いますか。',
      ['{宿題|しゅくだい}を して います', '{本|ほん}を {読|よ}んで います', 'スマホばかり {見|み}て {遊|あそ}んで います'], 2,
      'Apa yang dilakukan anak itu? — Hanya main ponsel dan bermain.',
      'What is the child doing? — Just looking at the phone and playing.'),
    q(6, [(Mx, '窓、閉めた？')],
      '答《こた》えは どれですか。',
      ['{窓|まど}は {大|おお}きいです。', '{閉|し}めた つもりだけど…。', '{閉|し}めて ください。'], 1,
      'Jendelanya sudah ditutup? — Rasanya sudah...',
      'Did you close the window? — I thought I did...'),
    q(7, [(F, '夏休みは 何を する 予定？')],
      '答《こた》えは どれですか。',
      ['{海|うみ}へ {行|い}くとか、{山|やま}に {登|のぼ}るとか {考|かんが}えて いるよ。', '{去年|きょねん} {海|うみ}へ {行|い}きました。', '{夏休|なつやす}みは {暑|あつ}いです。'], 0,
      'Rencana libur musim panas? — Ke laut atau naik gunung, begitu.',
      'What are your summer plans? — Maybe the sea, maybe mountain climbing.'),
    q(8, [(Mx, 'あれ、だんだん 暗く なって きたね。'),
          (F, '本当だ。雨が 降って くるかも しれない。')],
      'これから 雨《あめ》が 降《ふ》るかも しれません。', OX, 0,
      'Mungkin sebentar lagi hujan. → ○',
      'It might rain soon. → ○'),
    q(9, [(F, '田中さんは どこ？'),
          (Mx, 'さっき 資料を 持って 出て いきましたよ。')],
      '田中《たなか》さんは 今《いま》 ここに います。', OX, 1,
      'Tanaka sekarang ada di sini. → ×',
      'Tanaka is here now. → ×'),
    q(10, [(Mx, '昨日の ニュースで、こんな 話を 聞きました。この 町に 大きな 図書館が できるそうです。'),
           (F, 'それは いい ニュースですね。')],
      '町《まち》に 病院《びょういん》が できる ニュースを 聞《き》きました。', OX, 1,
      'Ia mendengar berita akan ada rumah sakit di kota. → ×',
      'He heard news that a hospital will be built in the town. → ×'),
]

kaiwa_turns = [
    (F, 'ビジネスマナーの セミナーの 案内が 来ましたよ。'),
    (Mx, '費用は いくらですか。'),
    (F, '全額 会社が 出して くれます。'),
    (Mx, 'それは いいですね。日時は？'),
    (F, '来月の 十日です。'),
    (Mx, 'ぜひ 参加したいです。'),
]
kaiwa_qs = [
    {'id': f'{L}-kq1', 'prompt': ja('セミナーの {費用|ひよう}は だれが {出|だ}しますか。'.replace('{費用|ひよう}','費用《ひよう》').replace('{出|だ}','出《だ》')),
     'choices': [ja(c) for c in ['{自分|じぶん}', '{会社|かいしゃ}', '{先生|せんせい}']], 'answer': 1, 'audio': None,
     'translate': {'id': 'Siapa yang membayar biaya seminar? — Perusahaan.', 'en': 'Who pays for the seminar? — The company.'}},
    {'id': f'{L}-kq2', 'prompt': ja('セミナーは いつですか。'),
     'choices': [ja(c) for c in ['{来月|らいげつ}の {十日|とおか}', '{来週|らいしゅう}', '{今月|こんげつ}の {十日|とおか}']], 'answer': 0, 'audio': None,
     'translate': {'id': 'Kapan seminarnya? — Tanggal 10 bulan depan.', 'en': 'When is the seminar? — The 10th of next month.'}},
]

chokai = {
    'id': 'n3-6', 'level': 'N3',
    'focus': 'N3 Pelajaran 6 — …て／…って, Xって…, Vる つもりは ない, つもりだった, Vた／Vている つもり, Vて ばかり いる／Nばかり, …とか…とか, Vて くる／いく, こ〜',
    'kaiwa': {'id': 'kaiwa',
              'audio_text': [{'speaker': s, 'text': t} for s, t in kaiwa_turns],
              'audio': '/audio/chokai/lesson-n3-6/kaiwa.mp3',
              'questions': kaiwa_qs},
    'questions': questions,
}
dump('public/data/chokai/lesson-n3-6.json', chokai)


def turn(sid, n, who, text, idt, ent):
    d = {'id': f'{sid}-t{n}', 'who': who, 'ja': ja(text), 'speak': speak(text),
         'id_text': idt, 'en_text': ent, 'audio': f'/audio/kaiwa/lesson-n3-6/{sid}-t{n}.mp3'}
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
    scenario(1, 'Kata Tanaka tadi', 'What Tanaka said',
             'Di kantor. Rekan dekat bertanya apa kata Tanaka kepadamu.',
             'At the office. A close colleague asks what Tanaka told you.',
             'Menyampaikan ucapan orang lain dengan …って.', 'Report what someone said with ...tte.',
             ['…て／…って'], '佐藤', 'male', [
        (P, '{田中|たなか}さんは {何|なん}て {言|い}って いた？', 'Tanaka bilang apa?', 'What did Tanaka say?'),
        (Y, '{今日|きょう}は {早|はや}く {帰|かえ}るって。', 'Katanya hari ini pulang cepat.', 'He says he is going home early today.'),
        (P, '{理由|りゆう}は？', 'Alasannya?', 'The reason?'),
        (Y, '{子|こ}どもが {熱|ねつ}を {出|だ}したって {言|い}って いたよ。', 'Katanya anaknya demam.', 'He said his child has a fever.'),
        (P, 'そう。{大変|たいへん}だね。', 'Begitu. Repot ya.', 'I see. That is tough.'),
        (Y, '{明日|あした}は {来|く}るって。', 'Katanya besok dia masuk.', 'He says he will come in tomorrow.')]),
    scenario(2, 'Sapporo itu kota seperti apa?', 'What kind of town is Sapporo?',
             'Istirahat siang. Kamu bertanya kepada teman yang pernah ke Hokkaido.',
             'Lunch break. You ask a friend who has been to Hokkaido.',
             'Menanyakan topik dengan Xって、どんな〜？.', 'Ask about a topic with X tte, donna ~?',
             ['Xって…'], '山田', 'female', [
        (Y, '{札幌|さっぽろ}って、どんな {町|まち}？', 'Sapporo itu kota seperti apa?', 'What kind of town is Sapporo?'),
        (P, '{冬|ふゆ}は {雪|ゆき}が {多|おお}くて、{食|た}べ{物|もの}が おいしい {町|まち}だよ。', 'Musim dingin banyak salju, makanannya enak.', 'It has lots of snow in winter and great food.'),
        (Y, '{行|い}った ことが あるの？', 'Kamu pernah ke sana?', 'Have you been there?'),
        (P, 'うん、{去年|きょねん} {行|い}った。', 'Iya, tahun lalu.', 'Yes, last year.'),
        (Y, 'いいなあ。{私|わたし}も {行|い}って みたい。', 'Enak ya. Saya juga ingin pergi.', 'Lucky you. I want to go too.'),
        (P, 'ぜひ {行|い}って みて。', 'Cobalah pergi.', 'You should definitely go.')]),
    scenario(3, 'Tidak berniat datang ke reuni', 'No intention of going to the reunion',
             'Sore hari. Teman lama menanyakan reuni bulan depan.',
             'Evening. An old friend asks about next month\'s reunion.',
             'Menyatakan tidak berniat dengan つもりは ない dan menjelaskan niat yang batal dengan つもりだった.', 'State no intention with tsumori wa nai and a failed plan with tsumori datta.',
             ['Vる つもりは ない', 'Vる つもりだった'], '木村', 'male', [
        (P, '{来月|らいげつ}の {同窓会|どうそうかい}、{行|い}く？', 'Reuni bulan depan, kamu datang?', 'Are you going to next month\'s reunion?'),
        (Y, 'ううん、{行|い}く つもりは ないよ。', 'Tidak, aku tidak berniat datang.', 'No, I have no intention of going.'),
        (P, 'どうして？', 'Kenapa?', 'Why?'),
        (Y, '{仕事|しごと}が {忙|いそが}しいし、あまり {気|き}が {進|すす}まないんだ。', 'Pekerjaan sibuk, dan aku kurang berminat.', 'I am busy with work, and I do not feel like it.'),
        (P, '{昨日|きのう}の {会議|かいぎ}は？ {来|こ}なかったね。', 'Bagaimana dengan rapat kemarin? Kamu tidak datang.', 'What about yesterday\'s meeting? You did not come.'),
        (Y, 'ごめん、{行|い}く つもりだったんだけど、お{客|きゃく}さんが {来|き}て しまって。', 'Maaf, sebenarnya aku berniat datang, tapi ada tamu.', 'Sorry, I was going to come, but a customer showed up.')]),
    scenario(4, 'Jendela terbuka', 'The open window',
             'Di rumah. Kamu dan keluarga menyadari jendela terbuka dan langit mulai gelap.',
             'At home. You and a family member notice an open window and a darkening sky.',
             'Memakai Vた つもり dan Vて くる.', 'Use V-ta tsumori and V-te kuru.',
             ['Vた／Vている つもり', 'Vて くる'], '高橋', 'female', [
        (P, '{窓|まど}、{閉|し}めた？', 'Jendelanya sudah ditutup?', 'Did you close the window?'),
        (Y, '{閉|し}めた つもりだけど…あれ、{開|あ}いて いるね。', 'Rasanya sudah, tapi... lho, terbuka ya.', 'I thought I did... oh, it is open.'),
        (P, '{雨|あめ}が {入|はい}って くるよ。', 'Hujan masuk, lho.', 'The rain is coming in.'),
        (Y, 'ごめん、すぐ {閉|し}める。', 'Maaf, segera kututup.', 'Sorry, I will close it right now.'),
        (P, 'あれ、だんだん {暗|くら}く なって きたね。', 'Lho, makin gelap ya.', 'Hey, it is getting darker.'),
        (Y, '{本当|ほんとう}だ。{雨|あめ}が {降|ふ}って くるかも しれない。', 'Benar. Mungkin hujan turun.', 'True. It might start to rain.')]),
    scenario(5, 'Anak hanya main ponsel', 'A child who only plays on the phone',
             'Taman bermain. Kamu berbincang dengan sesama orang tua.',
             'At a playground. You chat with another parent.',
             'Memakai Nばかり／Vて ばかり いる dan とか…とか.', 'Use N bakari / V-te bakari iru and toka...toka.',
             ['Vて ばかり いる', '…とか…とか'], '中村', 'female', [
        (P, '{最近|さいきん}、うちの {子|こ}は スマホばかり {見|み}て いるんです。', 'Akhir-akhir ini anak saya hanya melihat ponsel.', 'Lately my child does nothing but look at his smartphone.'),
        (Y, '{宿題|しゅくだい}は して いますか。', 'Apakah PR-nya dikerjakan?', 'Does he do his homework?'),
        (P, 'それも {忘|わす}れて、{遊|あそ}んで ばかり いて…。', 'Itu pun lupa, hanya bermain terus...', 'He even forgets that, and just keeps playing...'),
        (Y, '{時間|じかん}を {決|き}めたら どうですか。', 'Bagaimana kalau ditetapkan jamnya?', 'Why not set a time limit?'),
        (P, 'そうですね。{夏休|なつやす}みは {海|うみ}へ {行|い}くとか、{山|やま}に {登|のぼ}るとか したいです。', 'Benar. Liburan musim panas ingin ke laut atau naik gunung.', 'True. For summer break I would like to go to the sea or climb a mountain.'),
        (Y, 'いいですね。{私|わたし}は レポートとか アルバイトとかで {忙|いそが}しそうです。', 'Bagus. Saya sepertinya sibuk dengan laporan dan kerja paruh waktu.', 'Nice. I look to be busy with reports and part-time work.')]),
    scenario(6, 'Ke mana Tanaka?', 'Where did Tanaka go?',
             'Di kantor. Kamu mencari Tanaka lalu membicarakan berita kemarin.',
             'At the office. You look for Tanaka and then mention yesterday\'s news.',
             'Memakai Vて いく／くる dan こ〜 untuk isi yang akan disebut.', 'Use V-te iku / kuru and ko- for content about to be introduced.',
             ['Vて くる／いく', 'こ〜'], '鈴木', 'male', [
        (Y, '{田中|たなか}さんは どこですか。', 'Tanaka di mana?', 'Where is Tanaka?'),
        (P, 'さっき {資料|しりょう}を {持|も}って {出|で}て いきましたよ。', 'Tadi dia keluar membawa materi.', 'He went out a little while ago with the materials.'),
        (Y, 'すぐ {帰|かえ}って きますか。', 'Apakah segera kembali?', 'Will he come back soon?'),
        (P, 'ええ、{十分|じゅっぷん}ぐらいで {戻|もど}って くると {思|おも}います。', 'Ya, saya kira kembali sekitar sepuluh menit.', 'Yes, I think he will be back in about ten minutes.'),
        (Y, '{昨日|きのう}の ニュースで、こんな {話|はなし}を {聞|き}きました。', 'Dalam berita kemarin, saya mendengar cerita seperti ini.', 'I heard a story like this on yesterday\'s news.'),
        (P, 'どんな {話|はなし}ですか。', 'Cerita seperti apa?', 'What kind of story?')]),
]
kaiwa = {'id': 6, 'level': 'N3',
         'title_id': 'Pelajaran 6 (N3): Kutipan, Niat, dan Kata Tunjuk',
         'title_en': 'N3 Lesson 6: Quotations, Intentions and Demonstratives',
         'scenarios': scenarios}
dump('public/data/kaiwa/lesson-n3-6.json', kaiwa)
dump('public/data/kaiwa/index-n3.json', {'lessons': [
    {'id': n, 'file': f'lesson-n3-{n}.json', 'track': 'lesson', 'level': 'N3'} for n in (1, 2, 3, 4, 5, 6)]})
print('ok', len(questions), len(scenarios))
