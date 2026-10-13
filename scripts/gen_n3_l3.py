#!/usr/bin/env python3
"""Pembuat berkas N3 Pelajaran 3 (chokai + kaiwa) dari penanda {漢字|かな}.
Jalankan dari root project:  python3 scripts/gen_n3_l3.py
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
L = 'n3l3'
AUD = '/audio/chokai/lesson-n3-3'
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
    q(1, [(Mx, '明日、早く 帰らせて もらえませんか。'),
          (F, 'どうしたんですか。'),
          (Mx, '子どもの 学校で 面談が あるんです。'),
          (F, 'わかりました。いいですよ。')],
      '男《おとこ》の 人《ひと》は どうして 早《はや》く 帰《かえ》りたいですか。',
      ['{病院|びょういん}に {行|い}くからです', '{子|こ}どもの {面談|めんだん}が あるからです', '{会議|かいぎ}が あるからです'], 1,
      'Mengapa laki-laki itu ingin pulang cepat? — Ada pertemuan orang tua di sekolah anaknya.',
      'Why does the man want to leave early? — He has a meeting at his child\'s school.'),
    q(2, [(F, '来月から 毎朝 走る ことに しました。'),
          (Mx, 'すごいですね。夜は 何か 食べない ことに していますか。'),
          (F, 'はい、甘い ものは 食べない ことに しています。')],
      '女《おんな》の 人《ひと》は 夜《よる》 何《なに》を 食《た》べない ことに して いますか。',
      ['{甘|あま}い もの', '{肉|にく}', 'パン'], 0,
      'Apa yang tidak dimakan perempuan itu pada malam hari? — Makanan manis.',
      'What does the woman not eat at night? — Sweet things.'),
    q(3, [(Mx, '来月 大阪へ 転勤する ことに なりました。'),
          (F, 'えっ、本当ですか。'),
          (Mx, 'ええ。会社では 三年ごとに 転勤する ことに なっているんです。')],
      '男《おとこ》の 人《ひと》は どうして 転勤《てんきん》しますか。',
      ['{自分|じぶん}で {決|き}めたからです', '{家|いえ}が {遠|とお}いからです', '{会社|かいしゃ}の きまりだからです'], 2,
      'Mengapa laki-laki itu dimutasi? — Karena aturan perusahaan.',
      'Why is the man being transferred? — It is a company rule.'),
    q(4, [(F, '息子には もっと 勉強して ほしいんです。'),
          (Mx, 'ゲームを よく しますか。'),
          (F, 'ええ。ゲームばかり しないで ほしいんですが。')],
      '女《おんな》の 人《ひと》は 息子《むすこ》に 何《なに》を して ほしいですか。',
      ['ゲームを して ほしいです', '{勉強|べんきょう}して ほしいです', '{早|はや}く {寝|ね}て ほしいです'], 1,
      'Apa harapan perempuan itu pada putranya? — Agar belajar.',
      'What does the woman want her son to do? — Study.'),
    q(5, [(Mx, 'あの 映画、見に 行きませんか。'),
          (F, 'あまり おもしろくなさそうですね。'),
          (Mx, 'じゃ、今日は 雨も やみそうも ありませんし、うちで DVDを 見ましょう。')],
      '二人《ふたり》は 今日《きょう》 何《なに》を しますか。',
      ['{映画館|えいがかん}で {映画|えいが}を {見|み}ます', '{家|いえ}で DVDを {見|み}ます', '{雨|あめ}の {中|なか}を {出|で}かけます'], 1,
      'Apa yang akan mereka lakukan hari ini? — Menonton DVD di rumah.',
      'What will they do today? — Watch a DVD at home.'),
    q(6, [(Mx, '明日 早く 帰らせて いただけませんか。')],
      '答《こた》えは どれですか。',
      ['はい、{帰|かえ}って いただきます。', 'いいえ、{帰|かえ}りました。', 'はい、いいですよ。'], 2,
      'Bolehkah saya pulang cepat besok? — Ya, boleh.',
      'May I leave early tomorrow? — Yes, that is fine.'),
    q(7, [(F, '会議が 終わった あと、少し 時間 ありますか。')],
      '答《こた》えは どれですか。',
      ['はい、{会議|かいぎ}の {前|まえ}です。', 'ええ、{大丈夫|だいじょうぶ}です。', 'いいえ、{終|お}わりました。'], 1,
      'Setelah rapat, ada waktu? — Ya, tidak masalah.',
      'Do you have time after the meeting? — Yes, that is fine.'),
    q(8, [(F, '見て！あの 皿、落ちそうですよ。'),
          (Mx, 'あっ、本当だ。ありがとう。')],
      '{皿|さら}は もう {落|お}ちました。'.replace('{皿|さら}','皿《さら》').replace('{落|お}','落《お》'), OX, 1,
      'Piringnya sudah jatuh. → ×', 'The plate has already fallen. → ×'),
    q(9, [(Mx, 'うちの 会社では 三年ごとに 転勤する ことに なっています。'),
          (F, '大変ですね。')],
      '男《おとこ》の 人《ひと》の 会社《かいしゃ》は 三年《さんねん》ごとに 転勤《てんきん》が あります。', OX, 0,
      'Perusahaan laki-laki itu ada mutasi tiap tiga tahun. → ○',
      'The man\'s company has transfers every three years. → ○'),
    q(10, [(F, '昼ごはんを 食べた あと、一緒に 資料を 確認しませんか。'),
           (Mx, 'いいですよ。')],
      '二人《ふたり》は 昼《ひる》ごはんの 前《まえ》に 資料《しりょう》を 確認《かくにん》します。', OX, 1,
      'Mereka memeriksa materi sebelum makan siang. → ×',
      'They will check the materials before lunch. → ×'),
]

kaiwa_turns = [
    (Mx, '山田です。明日の 研修の ことで お電話しました。'),
    (F, 'お電話、代わりました。どうかしましたか。'),
    (Mx, '急用が できて、明日 休ませて いただけないでしょうか。'),
    (F, '困りましたね。でも、わかりました。'),
    (Mx, '申し訳ありません。'),
    (F, '気に しないで ください。'),
]
kaiwa_qs = [
    {'id': f'{L}-kq1', 'prompt': ja('男《おとこ》の 人《ひと》は 何《なん》の ことで 電話《でんわ》しましたか。'),
     'choices': [ja(c) for c in ['{新製品|しんせいひん}', '{研修|けんしゅう}', '{引|ひ}っ{越|こ}し']], 'answer': 1, 'audio': None,
     'translate': {'id': 'Tentang apa laki-laki itu menelepon? — Pelatihan.', 'en': 'What did the man call about? — The training.'}},
    {'id': f'{L}-kq2', 'prompt': ja('男《おとこ》の 人《ひと》は どう したいですか。'),
     'choices': [ja(c) for c in ['{明日|あした} {休|やす}みたいです', '{明日|あした} {早|はや}く {来|き}たいです', '{来週|らいしゅう} {休|やす}みたいです']], 'answer': 0, 'audio': None,
     'translate': {'id': 'Apa yang ingin dilakukan laki-laki itu? — Ingin libur besok.', 'en': 'What does the man want? — To take tomorrow off.'}},
]

chokai = {
    'id': 'n3-3', 'level': 'N3',
    'focus': 'N3 Pelajaran 3 — V(さ)せて もらえませんか／いただけませんか, Vる／Vない ことに する・している, ことに なる・なっている, Vて ほしい／Vないで ほしい, Vそうだ／Aそうだ, なさそう／Vそうも ない, Vた あと',
    'kaiwa': {'id': 'kaiwa',
              'audio_text': [{'speaker': s, 'text': t} for s, t in kaiwa_turns],
              'audio': '/audio/chokai/lesson-n3-3/kaiwa.mp3',
              'questions': kaiwa_qs},
    'questions': questions,
}
dump('public/data/chokai/lesson-n3-3.json', chokai)


def turn(sid, n, who, text, idt, ent):
    d = {'id': f'{sid}-t{n}', 'who': who, 'ja': ja(text), 'speak': speak(text),
         'id_text': idt, 'en_text': ent, 'audio': f'/audio/kaiwa/lesson-n3-3/{sid}-t{n}.mp3'}
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
    scenario(1, 'Meminta izin pulang cepat', 'Asking permission to leave early',
             'Sore di kantor. Kamu perlu pulang lebih awal besok dan bicara dengan atasan.',
             'Afternoon at the office. You need to leave early tomorrow and talk to your boss.',
             'Meminta izin dengan Vさせて いただけませんか.', 'Ask permission with V-sasete itadakemasen ka.',
             ['V(さ)せて もらえませんか／いただけませんか'], '中村', 'female', [
        (Y, 'すみません、{明日|あした} {早|はや}く {帰|かえ}らせて いただけませんか。', 'Maaf, bolehkah saya pulang lebih awal besok?', 'Excuse me, could I leave early tomorrow?'),
        (P, 'どうしたんですか。', 'Ada apa?', 'What is the matter?'),
        (Y, '{子|こ}どもの {学校|がっこう}で {面談|めんだん}が あるんです。', 'Ada pertemuan di sekolah anak saya.', 'There is a meeting at my child\'s school.'),
        (P, 'わかりました。いいですよ。', 'Baik. Boleh.', 'I see. That is fine.'),
        (Y, 'ありがとうございます。{仕事|しごと}は {今日中|きょうじゅう}に {終|お}わらせます。', 'Terima kasih. Pekerjaan akan saya selesaikan hari ini.', 'Thank you. I will finish my work today.'),
        (P, 'お{願|ねが}いしますね。', 'Saya mengandalkanmu.', 'Please do.')]),
    scenario(2, 'Keputusan baru', 'A new resolution',
             'Istirahat siang. Rekan kerja bertanya soal kebiasaan barumu.',
             'Lunch break. A colleague asks about your new habit.',
             'Menyatakan keputusan dan kebiasaan dengan ことに する／している.', 'State decisions and habits with koto ni suru / shite iru.',
             ['Vる／Vない ことにする・している'], '佐藤', 'male', [
        (P, 'ブディさん、{最近|さいきん} {元気|げんき}ですね。', 'Budi, akhir-akhir ini kamu bersemangat.', 'Budi, you seem energetic lately.'),
        (Y, '{来月|らいげつ}から、{毎朝|まいあさ} {走|はし}る ことに しました。', 'Mulai bulan depan, saya memutuskan berlari tiap pagi.', 'I decided to run every morning from next month.'),
        (P, 'すごいですね。{食|た}べ{物|もの}は？', 'Hebat. Kalau makanan?', 'Impressive. What about food?'),
        (Y, '{夜|よる}は {甘|あま}い ものを {食|た}べない ことに して います。', 'Malam hari saya tidak makan yang manis.', 'I make it a rule not to eat sweets at night.'),
        (P, '{続|つづ}けられると いいですね。', 'Semoga bisa terus.', 'I hope you can keep it up.'),
        (Y, 'はい、がんばります。', 'Ya, saya akan berusaha.', 'Yes, I will do my best.')]),
    scenario(3, 'Mutasi ke Osaka', 'Transfer to Osaka',
             'Sore hari. Kamu memberi tahu teman kantor soal mutasi.',
             'Evening. You tell a colleague about your transfer.',
             'Menyatakan hal yang ditetapkan dengan ことに なる／なっている.', 'State what has been decided with koto ni naru / natte iru.',
             ['Vる ことになる・なっている'], '山田', 'female', [
        (Y, '{実|じつ}は、{来月|らいげつ} {大阪|おおさか}へ {転勤|てんきん}する ことに なりました。', 'Sebenarnya, bulan depan saya akan dimutasi ke Osaka.', 'Actually, I am being transferred to Osaka next month.'),
        (P, 'えっ、{本当|ほんとう}ですか。', 'Eh, benarkah?', 'What, really?'),
        (Y, 'ええ。うちの {会社|かいしゃ}では {三年|さんねん}ごとに {転勤|てんきん}する ことに なって いるんです。', 'Ya. Di perusahaan kami, ditetapkan mutasi tiap tiga tahun.', 'Yes. At our company, transfers happen every three years.'),
        (P, 'そうですか。{寂|さび}しく なりますね。', 'Begitu. Jadi sepi, ya.', 'I see. It will feel lonely.'),
        (Y, '{大阪|おおさか}にも ぜひ {遊|あそ}びに {来|き}て ください。', 'Silakan main juga ke Osaka.', 'Please do come visit me in Osaka.'),
        (P, 'はい、{楽|たの}しみに して います。', 'Ya, saya menantikannya.', 'Yes, I am looking forward to it.')]),
    scenario(4, 'Harapan kepada anak', 'Hopes for a child',
             'Di taman bermain. Kamu ngobrol dengan sesama orang tua.',
             'At a playground. You chat with another parent.',
             'Menyatakan harapan dengan Vて ほしい／Vないで ほしい.', 'Express wishes with V-te hoshii / V-naide hoshii.',
             ['Vて ほしい', 'Vないで ほしい'], '木村', 'male', [
        (P, '{息子|むすこ}さんは {元気|げんき}ですか。', 'Apakah putra Anda sehat?', 'Is your son doing well?'),
        (Y, 'はい。でも、もっと {勉強|べんきょう}して ほしいんです。', 'Ya. Tapi saya ingin dia belajar lebih giat.', 'Yes. But I want him to study more.'),
        (P, '{何|なに}か {心配|しんぱい}な ことが あるんですか。', 'Ada yang dikhawatirkan?', 'Is there something worrying you?'),
        (Y, 'ゲームばかり しないで ほしいんですが…。', 'Saya ingin dia tidak hanya main game...', 'I wish he would not play only games...'),
        (P, 'うちも {同|おな}じです。', 'Di rumah kami juga sama.', 'It is the same at our house.'),
        (Y, 'そうですか。{大変|たいへん}ですね。', 'Begitu. Repot, ya.', 'I see. It is hard, is it not?')]),
    scenario(5, 'Kelihatannya enak, tapi...', 'Looks tasty, but...',
             'Akhir pekan. Kamu dan teman memilih apa yang dilakukan hari ini.',
             'Weekend. You and a friend decide what to do today.',
             'Menyatakan kesan dengan そう／なさそう／そうも ない.', 'Express impressions with sou / nasasou / sou mo nai.',
             ['Vそうだ／Aそうだ', 'なさそう／Vそうも ない'], '高橋', 'female', [
        (P, '{見|み}て！このケーキ、おいしそうですね。', 'Lihat! Kue ini kelihatannya enak.', 'Look! This cake looks delicious.'),
        (Y, '{本当|ほんとう}ですね。でも、その {皿|さら}は テーブルから {落|お}ちそうですよ。', 'Benar. Tapi piring itu hampir jatuh dari meja.', 'It does. But that plate looks about to fall off the table.'),
        (P, 'あっ、ありがとう。{映画|えいが}は どうしますか。', 'Ah, terima kasih. Bagaimana dengan film?', 'Oh, thanks. What about the movie?'),
        (Y, 'あの {映画|えいが}は あまり おもしろくなさそうですね。', 'Film itu kelihatannya kurang menarik.', 'That movie does not look very interesting.'),
        (P, '{雨|あめ}も やみそうも ありませんね。', 'Hujan juga sepertinya tidak akan reda.', 'And the rain does not look like it will stop either.'),
        (Y, 'じゃ、うちで DVDを {見|み}ましょう。', 'Kalau begitu, kita nonton DVD di rumah.', 'Then let us watch a DVD at home.')]),
    scenario(6, 'Setelah rapat', 'After the meeting',
             'Menjelang siang. Kamu mengajak rekan memeriksa materi.',
             'Near noon. You ask a colleague to check the materials with you.',
             'Mengatur urutan dengan Vた あと.', 'Order events with V-ta ato.',
             ['Vた あと、〜'], '鈴木', 'male', [
        (Y, '{会議|かいぎ}が {終|お}わった あと、ちょっと {時間|じかん} ありますか。', 'Setelah rapat selesai, ada waktu sebentar?', 'After the meeting, do you have a little time?'),
        (P, 'ええ、{大丈夫|だいじょうぶ}です。', 'Ya, tidak masalah.', 'Yes, that is fine.'),
        (Y, '{昼|ひる}ごはんを {食|た}べた あと、{一緒|いっしょ}に {資料|しりょう}を {確認|かくにん}しませんか。', 'Setelah makan siang, mau memeriksa materi bersama?', 'After lunch, shall we check the materials together?'),
        (P, 'いいですよ。{何時|なんじ}に しましょうか。', 'Boleh. Jam berapa?', 'Sure. What time shall we do it?'),
        (Y, '{一時|いちじ}は どうですか。', 'Bagaimana kalau jam satu?', 'How about one o\'clock?'),
        (P, 'はい、{一時|いちじ}に {会議室|かいぎしつ}で。', 'Ya, jam satu di ruang rapat.', 'Okay, at one in the meeting room.')]),
]
kaiwa = {'id': 3, 'level': 'N3',
         'title_id': 'Pelajaran 3 (N3): Izin, Keputusan, dan Harapan',
         'title_en': 'N3 Lesson 3: Permission, Decisions and Hopes',
         'scenarios': scenarios}
dump('public/data/kaiwa/lesson-n3-3.json', kaiwa)
dump('public/data/kaiwa/index-n3.json', {'lessons': [
    {'id': n, 'file': f'lesson-n3-{n}.json', 'track': 'lesson', 'level': 'N3'} for n in (1, 2, 3)]})
print('ok', len(questions), len(scenarios))
