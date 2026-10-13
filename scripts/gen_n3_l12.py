#!/usr/bin/env python3
"""Pembuat berkas N3 Pelajaran 12 (chokai + kaiwa) dari penanda {漢字|かな}.
Jalankan dari root project:  python3 scripts/gen_n3_l12.py
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
L = 'n3l12'
AUD = '/audio/chokai/lesson-n3-12'
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
    q(1, [(F, 'どうして 宿題を 出して いないの？'),
          (Mx, 'すみません、昨日 熱が あったものですから。'),
          (F, 'そう。じゃあ、明日まで 待ちます。'),
          (Mx, 'ありがとうございます。')],
      '男《おとこ》の 人《ひと》は どうして 宿題《しゅくだい》を 出《だ》して いませんか。',
      ['{忘|わす}れたからです', '{時間|じかん}が なかったからです', '{熱|ねつ}が あったからです'], 2,
      'Mengapa laki-laki itu belum mengumpulkan PR? — Karena demam.',
      'Why has the man not handed in his homework? — He had a fever.'),
    q(2, [(F, '昨日、どうして 寝られなかったの？'),
          (Mx, '隣の 部屋の 人に 夜中まで 騒がれたんだ。'),
          (F, 'それは 大変だったね。'),
          (Mx, 'その うえ、帰り道で 雨にも 降られたし。')],
      '男《おとこ》の 人《ひと》は 昨日《きのう》 何《なに》に 困《こま》りましたか。',
      ['{仕事|しごと}と {雨|あめ}', '{隣|となり}の {人|ひと}の {騒音|そうおん}と {雨|あめ}', '{熱|ねつ}と {雨|あめ}'], 1,
      'Apa yang menyusahkan laki-laki itu kemarin? — Keributan tetangga dan hujan.',
      'What troubled the man yesterday? — The neighbor\'s noise and the rain.'),
    q(3, [(Mx, '顔色が 悪いけど、どうしたの？'),
          (F, '朝の 電車で 足を 踏まれて、まだ 痛いんだ。'),
          (Mx, '大丈夫？病院に 行った ほうが いいよ。')],
      '女《おんな》の 人《ひと》は どうして 顔色《かおいろ》が 悪《わる》いですか。',
      ['{熱|ねつ}が あるからです', '{寝|ね}て いないからです', '{足|あし}が {痛|いた}いからです'], 2,
      'Mengapa perempuan itu pucat? — Karena kakinya sakit.',
      'Why does the woman look pale? — Her foot hurts.'),
    q(4, [(F, '休みの 日は 何を して いますか。'),
          (Mx, '料理を 作ったり、映画を 見たり して います。'),
          (F, '外には 出ませんか。'),
          (Mx, '天気が よければ、公園を 散歩したり します。')],
      '男《おとこ》の 人《ひと》は 休《やす》みの 日《ひ》に 何《なに》を しますか。',
      ['{買|か}い{物|もの}や {旅行|りょこう}', '{料理|りょうり}や {映画|えいが}', '{勉強|べんきょう}や {仕事|しごと}'], 1,
      'Apa yang dilakukan laki-laki itu di hari libur? — Memasak dan menonton film.',
      'What does the man do on his days off? — Cooks and watches movies.'),
    q(5, [(Mx, 'あれ、窓が 開けっぱなしだよ。'),
          (F, 'ごめん、閉め忘れた。'),
          (Mx, '雨が 降ったら 部屋が 濡れるよ。'),
          (F, 'すぐ 閉めて くる。')],
      '女《おんな》の 人《ひと》は これから 何《なに》を しますか。',
      ['{傘|かさ}を {買|か}います', '{部屋|へや}を {掃除|そうじ}します', '{窓|まど}を {閉|し}めます'], 2,
      'Apa yang akan dilakukan perempuan itu? — Menutup jendela.',
      'What will the woman do next? — Close the window.'),
    q(6, [(Mx, '日本語の 試験、合格したそうですね。おめでとうございます。')],
      '答《こた》えは どれですか。',
      ['{先生|せんせい}の おかげです。ありがとうございます。', '{合格|ごうかく}しませんでした。', '{試験|しけん}は {難|むずか}しいです。'], 0,
      'Selamat atas kelulusan ujian bahasa Jepang. — Berkat guru, terima kasih.',
      'Congratulations on passing the Japanese exam. — Thanks to my teacher.'),
    q(7, [(F, '顔が 赤いけど、熱が あるの？')],
      '答《こた》えは どれですか。',
      ['うん、お{酒|さけ}は おいしいよ。', 'ううん、{昨日|きのう} お{酒|さけ}を {飲|の}みすぎた せいだよ。', 'いいえ、{顔|かお}は {白|しろ}いです。'], 1,
      'Wajahmu merah, demam ya? — Bukan, gara-gara kemarin minum kebanyakan.',
      'Your face is red; do you have a fever? — No, it is because I drank too much yesterday.'),
    q(8, [(Mx, '隣の 部屋、静かだね。電気も 消えて いるし、出かけた みたいだね。'),
          (F, '旅行かな。')],
      '隣《となり》の 人《ひと》は 出《で》かけた みたいです。', OX, 0,
      'Tetangga sepertinya sedang pergi. → ○',
      'The neighbor seems to have gone out. → ○'),
    q(9, [(F, '「家族が みんな 元気で いられますように」って 短冊に 書いたよ。'),
          (Mx, 'いいね。')],
      '女《おんな》の 人《ひと》は 試験《しけん》の ことを 短冊《たんざく》に 書《か》きました。', OX, 1,
      'Perempuan itu menulis tentang ujian di tanzaku. → ×',
      'The woman wrote about her exam on the tanzaku. → ×'),
    q(10, [(Mx, '音が 下まで 聞こえるとは 思いませんでした。すみません。'),
           (F, 'これから 気を つけて くださいね。')],
      '男《おとこ》の 人《ひと》は 音《おと》が 下《した》まで 聞《き》こえると 思《おも》いませんでした。', OX, 0,
      'Laki-laki itu tidak menyangka suaranya terdengar sampai bawah. → ○',
      'The man did not think the sound would carry downstairs. → ○'),
]

kaiwa_turns = [
    (F, '夜遅くまで 音楽が うるさくて、眠れませんでした。'),
    (Mx, 'すみません。壁が こんなに うすいとは 気が 付きませんでした。'),
    (F, '苦情を 言いたくは ないんですが、お願いします。'),
    (Mx, 'これからは 気を つけます。'),
    (F, 'ありがとうございます。'),
    (Mx, 'こちらこそ、迷惑を かけて すみませんでした。'),
]
kaiwa_qs = [
    {'id': f'{L}-kq1', 'prompt': ja('女《おんな》の 人《ひと》は どうして 眠《ねむ》れませんでしたか。'),
     'choices': [ja(c) for c in ['{仕事|しごと}が {忙|いそが}しかったからです', '{隣|となり}の {音楽|おんがく}が うるさかったからです', '{熱|ねつ}が あったからです']], 'answer': 1, 'audio': None,
     'translate': {'id': 'Mengapa perempuan itu tidak bisa tidur? — Musik tetangga berisik.', 'en': 'Why could the woman not sleep? — The neighbor\'s music was loud.'}},
    {'id': f'{L}-kq2', 'prompt': ja('男《おとこ》の 人《ひと》は 何《なん》と 言《い》いましたか。'),
     'choices': [ja(c) for c in ['もう {音楽|おんがく}を {聞|き}かない', '{引|ひ}っ{越|こ}す', 'これからは {気|き}を つける']], 'answer': 2, 'audio': None,
     'translate': {'id': 'Apa kata laki-laki itu? — Mulai sekarang akan lebih berhati-hati.', 'en': 'What did the man say? — That he will be careful from now on.'}},
]

chokai = {
    'id': 'n3-12', 'level': 'N3',
    'focus': 'N3 Pelajaran 12 — …もの／もんだから, 〜(ら)れる(間接受身), 〜たり〜たり, 〜っぱなし, …おかげで, …せいで, …みたいです, どちらかと言えば〜ほうだ, 〜ますように, 〜とは',
    'kaiwa': {'id': 'kaiwa',
              'audio_text': [{'speaker': s, 'text': t} for s, t in kaiwa_turns],
              'audio': '/audio/chokai/lesson-n3-12/kaiwa.mp3',
              'questions': kaiwa_qs},
    'questions': questions,
}
dump('public/data/chokai/lesson-n3-12.json', chokai)


def turn(sid, n, who, text, idt, ent):
    d = {'id': f'{sid}-t{n}', 'who': who, 'ja': ja(text), 'speak': speak(text),
         'id_text': idt, 'en_text': ent, 'audio': f'/audio/kaiwa/lesson-n3-12/{sid}-t{n}.mp3'}
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
    scenario(1, 'Alasan belum mengumpulkan PR', 'Why the homework is late',
             'Di kelas. Guru menanyakan kenapa PR-mu belum dikumpulkan.',
             'In class. The teacher asks why your homework is not in.',
             'Menyampaikan alasan dengan 〜ものですから.', 'Give a reason with ~mono desu kara.',
             ['…もの／もんだから'], '山田', 'female', [
        (P, 'どうして {宿題|しゅくだい}を {出|だ}して いないの？', 'Mengapa PR-nya belum dikumpulkan?', 'Why have you not handed in your homework?'),
        (Y, 'すみません、{昨日|きのう} {熱|ねつ}が あったものですから。', 'Maaf, kemarin saya demam.', 'I am sorry, I had a fever yesterday.'),
        (P, 'そう。じゃあ、{明日|あした}まで {待|ま}ちます。', 'Begitu. Kalau begitu, saya tunggu sampai besok.', 'I see. Then I will wait until tomorrow.'),
        (Y, 'ありがとうございます。{明日|あした}は {必|かなら}ず {出|だ}します。', 'Terima kasih. Besok pasti saya kumpulkan.', 'Thank you. I will definitely hand it in tomorrow.'),
        (P, '{体|からだ}の {調子|ちょうし}は どう？', 'Bagaimana kondisi tubuhmu?', 'How are you feeling?'),
        (Y, 'もう {大丈夫|だいじょうぶ}です。{薬|くすり}を {飲|の}んだものですから。', 'Sudah baik. Karena saya sudah minum obat.', 'I am fine now, since I took medicine.')]),
    scenario(2, 'Gara-gara tetangga ribut', 'The noisy neighbor',
             'Pagi hari. Teman menanyakan kenapa kamu tampak lelah.',
             'Morning. A friend asks why you look tired.',
             'Memakai pasif tak langsung (intransitif) 〜(ら)れる.', 'Use the indirect passive (intransitive) ~(ra)reru.',
             ['〜(ら)れる(自動詞)'], '佐藤', 'male', [
        (P, '{昨日|きのう}、どうして {寝|ね}られなかったの？', 'Kenapa kemarin kamu tidak bisa tidur?', 'Why could you not sleep yesterday?'),
        (Y, '{隣|となり}の {部屋|へや}の {人|ひと}に {夜中|よなか}まで {騒|さわ}がれたんだ。', 'Tetangga di sebelah ribut sampai tengah malam.', 'The person next door made noise until midnight.'),
        (P, 'それは {大変|たいへん}だったね。', 'Wah, repot ya.', 'That must have been rough.'),
        (Y, 'その うえ、{帰|かえ}り{道|みち}で {雨|あめ}にも {降|ふ}られたし。', 'Apalagi di jalan pulang kehujanan.', 'On top of that, I got rained on on the way home.'),
        (P, '{苦情|くじょう}は {言|い}った？', 'Sudah mengajukan keluhan?', 'Did you complain?'),
        (Y, 'ううん、{言|い}って いない。{管理人|かんりにん}に {相談|そうだん}して みる。', 'Belum. Aku mau konsultasi ke pengelola.', 'No, not yet. I will talk to the manager.')]),
    scenario(3, 'Kaki terinjak di kereta', 'Stepped on in the train',
             'Di kantor. Teman melihat wajahmu pucat.',
             'At the office. A friend notices you look pale.',
             'Memakai pasif transitif 〜を 踏まれる dan 〜たり〜たり.', 'Use the transitive passive o fumareru and ~tari ~tari.',
             ['〜(ら)れる(他動詞)', '〜たり〜たり'], '高橋', 'female', [
        (P, '{顔色|かおいろ}が {悪|わる}いけど、どうしたの？', 'Wajahmu pucat, kenapa?', 'You look pale. What is wrong?'),
        (Y, '{朝|あさ}の {電車|でんしゃ}で {足|あし}を {踏|ふ}まれて、まだ {痛|いた}いんだ。', 'Di kereta pagi kakiku terinjak dan masih sakit.', 'My foot was stepped on in the morning train and it still hurts.'),
        (P, '{大丈夫|だいじょうぶ}？ {病院|びょういん}に {行|い}った ほうが いいよ。', 'Tidak apa-apa? Sebaiknya ke rumah sakit.', 'Are you okay? You should go to the hospital.'),
        (Y, 'うん、ありがとう。{休|やす}みの {日|ひ}に {行|い}って みる。', 'Iya, terima kasih. Hari libur aku coba pergi.', 'Yes, thanks. I will try going on my day off.'),
        (P, '{休|やす}みの {日|ひ}は {何|なに}を して いるの？', 'Hari libur biasanya melakukan apa?', 'What do you do on your days off?'),
        (Y, '{料理|りょうり}を {作|つく}ったり、{映画|えいが}を {見|み}たり して いるよ。', 'Memasak, menonton film, dan sebagainya.', 'I cook, watch movies and things like that.')]),
    scenario(4, 'Jendela terbuka dan berkat guru', 'An open window and thanks to my teacher',
             'Di rumah. Teman menegur jendela terbuka lalu menyinggung ujianmu.',
             'At home. A friend points out an open window, then mentions your exam.',
             'Memakai 〜っぱなし dan …おかげだ.', 'Use ~ppanashi and ...okage da.',
             ['〜っぱなし', '…おかげで／おかげだ'], '木村', 'male', [
        (P, 'あれ、{窓|まど}が {開|あ}けっぱなしだよ。', 'Lho, jendelanya dibiarkan terbuka.', 'Hey, the window has been left open.'),
        (Y, 'ごめん、{閉|し}め{忘|わす}れた。', 'Maaf, lupa menutup.', 'Sorry, I forgot to close it.'),
        (P, '{雨|あめ}が {降|ふ}ったら {部屋|へや}が {濡|ぬ}れるよ。', 'Kalau hujan, kamarnya basah.', 'If it rains, the room will get wet.'),
        (Y, 'すぐ {閉|し}めて くる。', 'Segera kututup.', 'I will close it right away.'),
        (P, '{日本語|にほんご}の {試験|しけん}、{合格|ごうかく}したそうだね。', 'Katanya kamu lulus ujian bahasa Jepang.', 'I heard you passed the Japanese exam.'),
        (Y, '{先生|せんせい}が {毎週|まいしゅう} {練習|れんしゅう}に つき{合|あ}って くださった おかげだよ。', 'Berkat guru yang menemani latihan tiap minggu.', 'It is thanks to my teacher accompanying my practice every week.')]),
    scenario(5, 'Wajah merah dan tetangga sepi', 'A red face and a quiet neighbor',
             'Pagi hari. Teman melihat wajahmu merah, lalu membicarakan tetangga.',
             'Morning. A friend sees your red face, then talks about the neighbor.',
             'Memakai …せいだ, …みたいだ, dan どちらかと言えば〜ほうだ.', 'Use ...sei da, ...mitai da and dochira ka to ieba ~ hou da.',
             ['…せいで／せいだ', '…みたいです', 'どちらかと 言えば〜ほうだ'], '中村', 'female', [
        (P, '{顔|かお}が {赤|あか}いけど、{熱|ねつ}が あるの？', 'Wajahmu merah, demam?', 'Your face is red. Do you have a fever?'),
        (Y, 'ううん、{昨日|きのう} お{酒|さけ}を {飲|の}みすぎた せいだよ。', 'Bukan, gara-gara kemarin minum kebanyakan.', 'No, it is because I drank too much yesterday.'),
        (P, '{隣|となり}の {部屋|へや}、{静|しず}かだね。', 'Kamar sebelah sepi ya.', 'The room next door is quiet.'),
        (Y, '{電気|でんき}も {消|き}えて いるし、{出|で}かけた みたいだね。', 'Lampu juga mati, sepertinya pergi.', 'The lights are off too, so they seem to have gone out.'),
        (P, 'お{酒|さけ}は よく {飲|の}みますか。', 'Apakah sering minum alkohol?', 'Do you drink often?'),
        (Y, 'どちらかと {言|い}えば、{飲|の}まない ほうです。', 'Kalau boleh dibilang, saya termasuk yang tidak minum.', 'If anything, I am on the side of not drinking.')]),
    scenario(6, 'Menulis harapan di tanzaku', 'Wishes on a tanzaku',
             'Festival Tanabata di kantor. Rekan bertanya apa yang kamu tulis.',
             'Tanabata festival at the office. A colleague asks what you wrote.',
             'Menulis harapan dengan 〜ますように dan menyatakan kaget dengan 〜とは.', 'Write wishes with ~masu you ni and express surprise with ~to wa.',
             ['〜ますように', '〜とは'], '鈴木', 'male', [
        (P, '{七夕|たなばた}の {短冊|たんざく}に {何|なに}を {書|か}いた？', 'Kamu menulis apa di tanzaku Tanabata?', 'What did you write on your Tanabata tanzaku?'),
        (Y, '「{家族|かぞく}が みんな {元気|げんき}で いられますように」って {書|か}いたよ。', 'Aku menulis "semoga seluruh keluarga sehat".', 'I wrote, "May my whole family stay healthy."'),
        (P, 'いいね。{私|わたし}は 「{試験|しけん}に {合格|ごうかく}できますように」と {書|か}いたよ。', 'Bagus. Aku menulis "semoga lulus ujian".', 'Nice. I wrote, "May I pass the exam."'),
        (Y, '{叶|かな}うと いいね。', 'Semoga terkabul.', 'I hope it comes true.'),
        (P, '{昨日|きのう}は {音|おと}が {下|した}まで {聞|き}こえて いたでしょう？', 'Kemarin suaranya terdengar sampai bawah, kan?', 'Yesterday the sound carried all the way downstairs, did it not?'),
        (Y, 'ええ、{音|おと}が {聞|き}こえるとは {思|おも}いませんでした。', 'Ya, saya tidak menyangka suaranya terdengar.', 'Yes, I never expected the sound to be heard.')]),
]
kaiwa = {'id': 12, 'level': 'N3',
         'title_id': 'Pelajaran 12 (N3): Alasan, Tetangga, dan Harapan',
         'title_en': 'N3 Lesson 12: Excuses, Neighbors and Wishes',
         'scenarios': scenarios}
dump('public/data/kaiwa/lesson-n3-12.json', kaiwa)
dump('public/data/kaiwa/index-n3.json', {'lessons': [
    {'id': n, 'file': f'lesson-n3-{n}.json', 'track': 'lesson', 'level': 'N3'} for n in range(1, 13)]})
print('ok', len(questions), len(scenarios))
