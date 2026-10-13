#!/usr/bin/env python3
"""Pembuat berkas N3 Pelajaran 11 (chokai + kaiwa) dari penanda {漢字|かな}.
Jalankan dari root project:  python3 scripts/gen_n3_l11.py
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
L = 'n3l11'
AUD = '/audio/chokai/lesson-n3-11'
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
    q(1, [(Mx, '最近、朝は 寒く なって きたね。'),
          (F, 'もう 十月だからね。これから もっと 寒く なって いくよ。'),
          (Mx, 'コートを 出さなきゃ。'),
          (F, '風邪を ひかないように、早めに 準備した ほうが いいよ。')],
      '女《おんな》の 人《ひと》は 何《なん》と 言《い》いましたか。',
      ['コートは いらない', '{今|いま}は まだ {寒|さむ}くない', '{早|はや}めに コートを {準備|じゅんび}した ほうが いい'], 2,
      'Apa kata perempuan itu? — Sebaiknya segera menyiapkan mantel.',
      'What did the woman say? — That he should prepare a coat early.'),
    q(2, [(Mx, '最近、よく 眠れないんだ。'),
          (F, '寝る 前に スマホを 見ないように したら どう？'),
          (Mx, 'そうだね。やって みる。'),
          (F, 'あと、軽い 運動を して みたら？')],
      '女《おんな》の 人《ひと》は 何《なに》を すすめましたか。',
      ['{薬|くすり}を {飲|の}む こと', '{寝|ね}る {前|まえ}に スマホを {見|み}ない こと', 'もっと {長|なが}く {寝|ね}る こと'], 1,
      'Apa yang disarankan perempuan itu? — Tidak melihat ponsel sebelum tidur.',
      'What did the woman recommend? — Not looking at the phone before bed.'),
    q(3, [(F, '会議は 対面と オンラインと、どちらが いいですか。'),
          (Mx, '移動する 時間を 考えると、行くより オンラインの ほうが 楽ですね。'),
          (F, 'でも、大事な 話は 画面で 話すより 会って 話した ほうが いいと 思います。'),
          (Mx, 'たしかに、そうですね。')],
      '女《おんな》の 人《ひと》は 大事《だいじ》な 話《はなし》を どう した ほうが いいと 思《おも》って いますか。',
      ['オンラインで {話|はな}す', 'メールで {伝|つた}える', '{会|あ}って {話|はな}す'], 2,
      'Bagaimana sebaiknya membicarakan hal penting menurut perempuan itu? — Bertemu langsung.',
      'How does the woman think important matters should be discussed? — In person.'),
    q(4, [(Mx, '新しい 先生は どんな 人？'),
          (F, '先生らしい 落ち着いた 人だよ。'),
          (Mx, '授業は おもしろい？'),
          (F, 'うん。子どもらしい 質問にも 丁寧に 答えて くれるよ。')],
      '新《あたら》しい 先生《せんせい》は どんな 先生《せんせい》ですか。',
      ['{派手|はで}な {先生|せんせい}', 'おもしろくない {先生|せんせい}', '{落|お}ち{着|つ}いた {先生|せんせい}'], 2,
      'Guru baru seperti apa? — Guru yang tenang.',
      'What kind of teacher is the new teacher? — A calm one.'),
    q(5, [(F, '田中さん、会社を 辞める らしいよ。'),
          (Mx, 'えっ、本当？ だれから 聞いたの？'),
          (F, '鈴木さんが 言って いたの。故郷に 帰る らしい。'),
          (Mx, '寂しく なるね。')],
      '田中《たなか》さんは どう する らしいですか。',
      ['{外国|がいこく}へ {行|い}く', '{会社|かいしゃ}を {続|つづ}ける', '{故郷|ふるさと}に {帰|かえ}る'], 2,
      'Katanya Tanaka akan bagaimana? — Pulang ke kampung halaman.',
      'What is Tanaka said to be doing? — Going back to his hometown.'),
    q(6, [(Mx, '今度の 飲み会、どこに する？')],
      '答《こた》えは どれですか。',
      ['{駅前|えきまえ}の {居酒屋|いざかや}なんか どう？', '{昨日|きのう} {行|い}きました。', '{飲|の}み{会|かい}は {楽|たの}しかった。'], 0,
      'Pesta minum berikutnya di mana? — Bagaimana kalau izakaya depan stasiun?',
      'Where shall we hold the next drinking party? — How about the izakaya in front of the station?'),
    q(7, [(Mx, '最近、よく 眠れないんです。')],
      '答《こた》えは どれですか。',
      ['よく {眠|ねむ}れました。', '{寝|ね}る {前|まえ}に スマホを {見|み}ないように したら どうですか。', '{寝|ね}る {時間|じかん}は {十時|じゅうじ}です。'], 1,
      'Akhir-akhir ini saya susah tidur. — Bagaimana kalau tidak melihat ponsel sebelum tidur?',
      'I cannot sleep well lately. — How about not looking at your phone before bed?'),
    q(8, [(Mx, '昨日は 傘を 持たずに 出かけて、濡れて しまいました。'),
          (F, '天気予報を 見なかったんですか。')],
      '男《おとこ》の 人《ひと》は 傘《かさ》を 持《も》って 出《で》かけました。', OX, 1,
      'Laki-laki itu membawa payung. → ×',
      'The man took an umbrella with him. → ×'),
    q(9, [(F, '山田さんは エンジニアとして 工場で 働いて います。'),
          (Mx, 'そうですか。')],
      '山田《やまだ》さんは 工場《こうじょう》で 働《はたら》いて います。', OX, 0,
      'Yamada bekerja di pabrik. → ○',
      'Yamada works at a factory. → ○'),
    q(10, [(Mx, 'この 町は 十年間 大きな 地震は 起こって いません。'),
           (F, 'でも、備えは 大切ですね。')],
      'この 町《まち》では 最近《さいきん》 大《おお》きな 地震《じしん》が ありました。', OX, 1,
      'Di kota ini baru-baru ini ada gempa besar. → ×',
      'This town had a big earthquake recently. → ×'),
]

kaiwa_turns = [
    (F, 'ツアーは パックツアーと 個人旅行と、どちらが いいですか。'),
    (Mx, 'パックツアーは 安くて 便利ですが、自由行動が 少ないです。'),
    (F, '個人旅行は 自由ですが、計画が 大変ですね。'),
    (Mx, '両方 考えて みましたが、個人旅行の ほうが いいと 思います。'),
    (F, 'じゃあ、個人旅行に しましょう。'),
    (Mx, 'はい、お願いします。'),
]
kaiwa_qs = [
    {'id': f'{L}-kq1', 'prompt': ja('パックツアーの いい {点|てん}は 何《なん》ですか。'.replace('{点|てん}','点《てん》')),
     'choices': [ja(c) for c in ['{自由行動|じゆうこうどう}が {多|おお}い こと', '{安|やす}くて {便利|べんり}な こと', '{計画|けいかく}が {簡単|かんたん}な こと']], 'answer': 1, 'audio': None,
     'translate': {'id': 'Apa kelebihan tur paket? — Murah dan praktis.', 'en': 'What is good about a package tour? — It is cheap and convenient.'}},
    {'id': f'{L}-kq2', 'prompt': ja('二人《ふたり》は どちらに しますか。'),
     'choices': [ja(c) for c in ['パックツアー', 'まだ {決|き}めて いない', '{個人旅行|こじんりょこう}']], 'answer': 2, 'audio': None,
     'translate': {'id': 'Mana yang mereka pilih? — Perjalanan perorangan.', 'en': 'Which do they choose? — An individual trip.'}},
]

chokai = {
    'id': 'n3-11', 'level': 'N3',
    'focus': 'N3 Pelajaran 11 — 〜て くる・〜て いく, 〜たら どう？, …より…ほうが…, Nらしい, …らしい, Nとして, 〜ず(に), 〜ず、…, 〜て いる(経験), Nなんか どう？',
    'kaiwa': {'id': 'kaiwa',
              'audio_text': [{'speaker': s, 'text': t} for s, t in kaiwa_turns],
              'audio': '/audio/chokai/lesson-n3-11/kaiwa.mp3',
              'questions': kaiwa_qs},
    'questions': questions,
}
dump('public/data/chokai/lesson-n3-11.json', chokai)


def turn(sid, n, who, text, idt, ent):
    d = {'id': f'{sid}-t{n}', 'who': who, 'ja': ja(text), 'speak': speak(text),
         'id_text': idt, 'en_text': ent, 'audio': f'/audio/kaiwa/lesson-n3-11/{sid}-t{n}.mp3'}
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
    scenario(1, 'Pagi mulai dingin', 'Mornings are getting cold',
             'Di kantor pada bulan Oktober. Rekan kerja menyadari cuaca mulai dingin.',
             'At the office in October. A colleague notices the weather turning cold.',
             'Membicarakan perubahan dengan 〜て くる／いく.', 'Talk about change with ~te kuru / iku.',
             ['〜て くる・〜て いく'], '中村', 'female', [
        (P, '{最近|さいきん}、{朝|あさ}は {寒|さむ}く なって きましたね。', 'Akhir-akhir ini pagi hari mulai dingin ya.', 'Mornings have gotten cold lately.'),
        (Y, 'もう {十月|じゅうがつ}ですからね。これから もっと {寒|さむ}く なって いきますよ。', 'Sudah Oktober. Mulai sekarang akan makin dingin.', 'It is already October. It will keep getting colder.'),
        (P, 'コートを {出|だ}さなきゃ。', 'Harus mengeluarkan mantel.', 'I need to get out my coat.'),
        (Y, '{風邪|かぜ}を ひかないように、{早|はや}めに {準備|じゅんび}した ほうが いいですよ。', 'Supaya tidak masuk angin, sebaiknya disiapkan lebih awal.', 'You should prepare early so you do not catch a cold.'),
        (P, 'そうですね。{去年|きょねん}は {遅|おそ}かったんです。', 'Benar. Tahun lalu saya terlambat.', 'True. I was late last year.'),
        (Y, '{今年|ことし}は {早|はや}めに {出|だ}して おきましょう。', 'Tahun ini kita keluarkan lebih awal.', 'Let us get it out early this year.')]),
    scenario(2, 'Susah tidur', 'Trouble sleeping',
             'Istirahat siang. Teman dekat mengeluh susah tidur.',
             'Lunch break. A close friend complains of trouble sleeping.',
             'Memberi saran dengan 〜たら どう？ dan Nなんか どう？.', 'Offer suggestions with ~tara dou? and N nanka dou?',
             ['〜たら どう？／いかがですか', 'Nなんか どう？'], '佐藤', 'male', [
        (P, '{最近|さいきん}、よく {眠|ねむ}れないんだ。', 'Akhir-akhir ini sulit tidur.', 'I cannot sleep well lately.'),
        (Y, '{寝|ね}る {前|まえ}に スマホを {見|み}ないように したら どう？', 'Bagaimana kalau sebelum tidur tidak melihat ponsel?', 'How about trying not to look at your phone before bed?'),
        (P, 'そうだね。やって みる。', 'Benar juga. Akan kucoba.', 'Right. I will try it.'),
        (Y, 'あと、{軽|かる}い {運動|うんどう}を して みたら？', 'Selain itu, coba olahraga ringan.', 'Also, why not try some light exercise?'),
        (P, 'どんな {運動|うんどう}が いいかな。', 'Olahraga apa yang bagus ya?', 'What kind of exercise would be good?'),
        (Y, '{散歩|さんぽ}なんか どう？', 'Bagaimana kalau jalan-jalan?', 'How about a walk?')]),
    scenario(3, 'Rapat daring atau tatap muka', 'Online or in-person meeting',
             'Di kantor. Kamu dan rekan memilih cara rapat.',
             'At the office. You and a colleague choose the meeting format.',
             'Membandingkan dengan …より…ほうが….', 'Compare with ...yori ... hou ga ...',
             ['…より…ほうが…'], '山田', 'female', [
        (P, '{会議|かいぎ}は {対面|たいめん}と オンラインと、どちらが いいですか。', 'Rapat lebih baik tatap muka atau daring?', 'For the meeting, which is better, in person or online?'),
        (Y, '{移動|いどう}する {時間|じかん}を {考|かんが}えると、{行|い}くより オンラインの ほうが {楽|らく}ですね。', 'Mengingat waktu perjalanan, daring lebih mudah daripada datang.', 'Considering travel time, online is easier than going.'),
        (P, 'でも、{大事|だいじ}な {話|はなし}は…。', 'Tapi, untuk hal penting...', 'But for important matters...'),
        (Y, '{画面|がめん}で {話|はな}すより {会|あ}って {話|はな}した ほうが いいと {思|おも}います。', 'Saya kira lebih baik bertemu daripada bicara lewat layar.', 'I think meeting in person is better than talking on a screen.'),
        (P, 'たしかに、そうですね。', 'Benar juga.', 'True, that is so.'),
        (Y, 'では、{今回|こんかい}は {会|あ}って {話|はな}しましょう。', 'Kalau begitu, kali ini kita bertemu langsung.', 'Then let us meet and talk this time.')]),
    scenario(4, 'Guru baru dan kabar burung', 'The new teacher and a rumor',
             'Istirahat. Teman menanyakan guru baru lalu menyampaikan kabar.',
             'Break time. A friend asks about the new teacher, then shares a rumor.',
             'Memakai Nらしい (khas) dan …らしい (kabar).', 'Use N rashii (typical) and ...rashii (hearsay).',
             ['Nらしい', '…らしい'], '木村', 'male', [
        (P, '{新|あたら}しい {先生|せんせい}は どんな {人|ひと}？', 'Guru baru orangnya seperti apa?', 'What is the new teacher like?'),
        (Y, '{先生|せんせい}らしい {落|お}ち{着|つ}いた {人|ひと}だよ。', 'Orang tenang, khas seorang guru.', 'A calm person, just like a teacher should be.'),
        (P, '{授業|じゅぎょう}は おもしろい？', 'Pelajarannya seru?', 'Are the classes interesting?'),
        (Y, 'うん。{子|こ}どもらしい {質問|しつもん}にも {丁寧|ていねい}に {答|こた}えて くれるよ。', 'Ya. Pertanyaan khas anak-anak pun dijawab dengan sopan.', 'Yes. He even answers childlike questions politely.'),
        (P, '{田中|たなか}さん、{会社|かいしゃ}を {辞|や}める らしいよ。', 'Katanya Tanaka keluar dari perusahaan.', 'I hear Tanaka is quitting the company.'),
        (Y, 'そうなんだ。{鈴木|すずき}さんも {帰|かえ}る らしいよ。', 'Begitu. Katanya Suzuki juga pulang.', 'Is that so. I hear Suzuki is going home too.')]),
    scenario(5, 'Bekerja sebagai insinyur', 'Working as an engineer',
             'Di kantor. Rekan bertanya alasanmu datang ke Jepang.',
             'At the office. A colleague asks why you came to Japan.',
             'Menyatakan peran dengan Nとして dan pengalaman dengan 〜て いる.', 'State roles with N toshite and experience with ~te iru.',
             ['Nとして', '〜て いる(経験・記録)'], '高橋', 'female', [
        (P, '{山田|やまだ}さんは どうして {日本|にほん}へ {来|き}たんですか。', 'Mengapa Yamada datang ke Jepang?', 'Why did you come to Japan, Yamada?'),
        (Y, 'エンジニアとして {工場|こうじょう}で {働|はたら}く ためです。', 'Untuk bekerja di pabrik sebagai insinyur.', 'To work at a factory as an engineer.'),
        (P, '{仕事|しごと}は どうですか。', 'Bagaimana pekerjaannya?', 'How is the work?'),
        (Y, '{外国人|がいこくじん}の {社員|しゃいん}として、{学|まな}ぶ ことが たくさん あります。', 'Sebagai karyawan asing, banyak yang bisa dipelajari.', 'As a foreign employee, there is a lot to learn.'),
        (P, 'この {町|まち}は {地震|じしん}が {多|おお}いですか。', 'Apakah kota ini sering gempa?', 'Does this town have many earthquakes?'),
        (Y, '{過去|かこ}に {大|おお}きな {地震|じしん}が {二回|にかい} {起|お}こって います。', 'Di masa lalu pernah terjadi gempa besar dua kali.', 'Two big earthquakes have occurred in the past.')]),
    scenario(6, 'Tidak datang ke rapat', 'Missing the meeting',
             'Pagi hari. Rekan menanyakan kenapa kamu tidak datang ke rapat kemarin.',
             'Morning. A colleague asks why you missed yesterday\'s meeting.',
             'Menjelaskan sebab dan keadaan dengan 〜ず、…／〜ずに….', 'Explain causes and circumstances with ~zu, ... / ~zu ni ...',
             ['〜ず、…', '〜ず(に)…'], '鈴木', 'male', [
        (P, '{昨日|きのう}の {会議|かいぎ}に どうして {来|こ}なかったの？', 'Kenapa kamu tidak datang ke rapat kemarin?', 'Why did you not come to yesterday\'s meeting?'),
        (Y, '{電車|でんしゃ}が {動|うご}かず、{会社|かいしゃ}に {着|つ}けなかったんです。', 'Kereta tidak jalan, jadi tidak bisa sampai kantor.', 'The trains were not running, so I could not reach the office.'),
        (P, '{連絡|れんらく}は？', 'Menghubungi?', 'Did you contact us?'),
        (Y, '{電話|でんわ}も {通|つう}じず、{連絡|れんらく}できませんでした。', 'Teleponnya juga tidak tersambung, jadi tidak bisa menghubungi.', 'The phone would not connect either, so I could not contact you.'),
        (P, '{天気予報|てんきよほう}は {見|み}ましたか。', 'Apakah sudah melihat ramalan cuaca?', 'Did you check the weather forecast?'),
        (Y, 'いいえ、{確|たし}かめずに {家|いえ}を {出|で}て しまいました。', 'Belum, saya keluar rumah tanpa memeriksa.', 'No, I left home without checking.')]),
]
kaiwa = {'id': 11, 'level': 'N3',
         'title_id': 'Pelajaran 11 (N3): Perubahan, Saran, dan Perbandingan',
         'title_en': 'N3 Lesson 11: Change, Suggestions and Comparison',
         'scenarios': scenarios}
dump('public/data/kaiwa/lesson-n3-11.json', kaiwa)
dump('public/data/kaiwa/index-n3.json', {'lessons': [
    {'id': n, 'file': f'lesson-n3-{n}.json', 'track': 'lesson', 'level': 'N3'} for n in range(1, 12)]})
print('ok', len(questions), len(scenarios))
