#!/usr/bin/env python3
"""Pembuat berkas N3 Pelajaran 7 (chokai + kaiwa) dari penanda {漢字|かな}.
Jalankan dari root project:  python3 scripts/gen_n3_l7.py
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
L = 'n3l7'
AUD = '/audio/chokai/lesson-n3-7'
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
    q(1, [(Mx, '明日の 会議、私も 出なくては いけませんか。'),
          (F, '部長は 全員 出席しなくては ならないと 言って いましたよ。'),
          (Mx, '資料は 準備しなくても かまいませんか。'),
          (F, 'ええ、資料は 私が 用意します。')],
      '男《おとこ》の 人《ひと》は 資料《しりょう》を 準備《じゅんび》しなくては いけませんか。',
      ['{準備|じゅんび}しなくては いけません', '{準備|じゅんび}しなくても いいです', 'まだ わかりません'], 1,
      'Apakah laki-laki itu harus menyiapkan materi? — Tidak perlu.',
      'Does the man have to prepare the materials? — No, he does not.'),
    q(2, [(F, 'そろそろ 行かなくちゃ。'),
          (Mx, 'えっ、もう？'),
          (F, 'うん、五時までに 銀行に 行かなきゃ いけないの。'),
          (Mx, 'じゃ、また 今度ね。')],
      '女《おんな》の 人《ひと》は どうして もう 行《い》きますか。',
      ['{友達|ともだち}に {会|あ}うからです', '{会議|かいぎ}が あるからです', '{銀行|ぎんこう}に {行|い}かなければ ならないからです'], 2,
      'Mengapa perempuan itu pergi sekarang? — Karena harus ke bank.',
      'Why is the woman leaving now? — She has to go to the bank.'),
    q(3, [(Mx, 'この 書類、どう 書けば いいですか。'),
          (F, '名前と 住所を 書くだけで いいですよ。'),
          (Mx, 'ほかの 欄は？'),
          (F, '空けて おくだけで 大丈夫です。')],
      'ほかの 欄《らん》は どう しますか。',
      ['{空|あ}けて おきます', '{書|か}きます', '{消|け}します'], 0,
      'Kolom lain diapakan? — Dibiarkan kosong.',
      'What should be done with the other fields? — Leave them blank.'),
    q(4, [(F, '母の 誕生日に 何を あげようかな。'),
          (Mx, '花は どうかな。'),
          (F, 'いいね。あ、花屋まで 一緒に 来て くれないかな。'),
          (Mx, 'いいよ。')],
      '二人《ふたり》は これから どこへ 行《い》きますか。',
      ['{銀行|ぎんこう}', '{花屋|はなや}', '{会社|かいしゃ}'], 1,
      'Ke mana mereka akan pergi? — Ke toko bunga.',
      'Where will they go next? — The flower shop.'),
    q(5, [(Mx, '田中さんの 絵、とても 上手ですね。'),
          (F, 'いえ、私の 絵なんか、まだまだです。'),
          (Mx, '展覧会に 出して みませんか。'),
          (F, '私なんかが 出しても いいんでしょうか。')],
      '女《おんな》の 人《ひと》は 自分《じぶん》の 絵《え》に ついて どう 思《おも》って いますか。',
      ['とても {上手|じょうず}だと {思|おも}って います', '{展覧会|てんらんかい}に {出|だ}したいと {思|おも}って います', 'まだまだだと {思|おも}って います'], 2,
      'Bagaimana perempuan itu menilai lukisannya? — Masih jauh dari bagus.',
      'How does the woman see her own paintings? — As still far from good.'),
    q(6, [(Mx, '聞きました？佐藤さんが 来月 結婚するそうですよ。')],
      '答《こた》えは どれですか。',
      ['はい、{結婚|けっこん}しました。', 'えっ、{結婚|けっこん}するなんて {全然|ぜんぜん} {知|し}りませんでした。', 'いいえ、{来月|らいげつ}です。'], 1,
      'Dengar tidak? Sato akan menikah bulan depan. — Eh, saya sama sekali tidak tahu.',
      'Did you hear Sato is getting married next month? — What, I had no idea.'),
    q(7, [(F, '北海道へ 旅行に 行く 予定なんです。')],
      '答《こた》えは どれですか。',
      ['{北海道|ほっかいどう}なら、{海|うみ}の {幸|さち}が おいしいですよ。', '{北海道|ほっかいどう}へ {行|い}きました。', '{予定|よてい}は ありません。'], 0,
      'Saya berencana ke Hokkaido. — Kalau Hokkaido, hasil lautnya enak.',
      'I am planning a trip to Hokkaido. — If it is Hokkaido, the seafood is great.'),
    q(8, [(Mx, '昨日の 試験、満点を 取って、先生を びっくりさせたよ。'),
          (F, 'すごい！')],
      '男《おとこ》の 人《ひと》は 試験《しけん》で 満点《まんてん》を 取《と》りました。', OX, 0,
      'Laki-laki itu mendapat nilai penuh. → ○',
      'The man got a perfect score on the test. → ○'),
    q(9, [(F, '昨日の 映画には 感動させられました。'),
          (Mx, '私は 泣かされましたよ。')],
      '男《おとこ》の 人《ひと》は この 映画《えいが》が きらいです。', OX, 1,
      'Laki-laki itu tidak suka film ini. → ×',
      'The man dislikes this movie. → ×'),
    q(10, [(Mx, '部長に 明日の 資料を 今日中に コピーして くれと 言われた。'),
           (F, '大変だね。')],
      '資料《しりょう》は 明日《あした》 コピーします。', OX, 1,
      'Materi disalin besok. → ×',
      'The materials will be copied tomorrow. → ×'),
]

kaiwa_turns = [
    (F, '来週の 送別会、行きますか？'),
    (Mx, 'せっかく 誘って いただいたのに、申し訳ありません。今回は 遠慮させて ください。'),
    (F, '何か 予定が あるんですか？'),
    (Mx, 'はい、国から 友達が 来るんです。'),
    (F, 'そうですか。わかりました。'),
    (Mx, 'すみません、また 誘って ください。'),
]
kaiwa_qs = [
    {'id': f'{L}-kq1', 'prompt': ja('男《おとこ》の 人《ひと》は 送別会《そうべつかい》に 行《い》きますか。'),
     'choices': [ja(c) for c in ['{行|い}きます', 'まだ わかりません', '{行|い}きません']], 'answer': 2, 'audio': None,
     'translate': {'id': 'Apakah laki-laki itu ke pesta perpisahan? — Tidak.', 'en': 'Is the man going to the farewell party? — No.'}},
    {'id': f'{L}-kq2', 'prompt': ja('男《おとこ》の 人《ひと》は どうして 行《い》けませんか。'),
     'choices': [ja(c) for c in ['{国|くに}から {友達|ともだち}が {来|く}るからです', '{仕事|しごと}が {忙|いそが}しいからです', '{病気|びょうき}だからです']], 'answer': 0, 'audio': None,
     'translate': {'id': 'Mengapa ia tidak bisa pergi? — Karena teman dari kampung halamannya datang.', 'en': 'Why can he not go? — A friend is coming from his home country.'}},
]

chokai = {
    'id': 'n3-7', 'level': 'N3',
    'focus': 'N3 Pelajaran 7 — Vなくては ならない／いけない・Vなくても かまわない, Vなくちゃ／Vなきゃ, Nだけだ／Vるだけでいい, …かな, Nなんか, …なんて, Vi(さ)せる, Vi(さ)せられる, …なら, Vて くれ',
    'kaiwa': {'id': 'kaiwa',
              'audio_text': [{'speaker': s, 'text': t} for s, t in kaiwa_turns],
              'audio': '/audio/chokai/lesson-n3-7/kaiwa.mp3',
              'questions': kaiwa_qs},
    'questions': questions,
}
dump('public/data/chokai/lesson-n3-7.json', chokai)


def turn(sid, n, who, text, idt, ent):
    d = {'id': f'{sid}-t{n}', 'who': who, 'ja': ja(text), 'speak': speak(text),
         'id_text': idt, 'en_text': ent, 'audio': f'/audio/kaiwa/lesson-n3-7/{sid}-t{n}.mp3'}
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
    scenario(1, 'Apakah saya harus hadir?', 'Do I have to attend?',
             'Di kantor. Kamu menanyakan kewajiban untuk rapat besok.',
             'At the office. You ask about your obligations for tomorrow\'s meeting.',
             'Menanyakan kewajiban dan kelonggaran dengan なくては いけない／なくても かまわない.', 'Ask about obligations and leniency with nakute wa ikenai / nakute mo kamawanai.',
             ['Vなくては ならない／いけない', 'Vなくても かまわない'], '中村', 'female', [
        (Y, '{明日|あした}の {会議|かいぎ}、{私|わたし}も {出|で}なくては いけませんか。', 'Rapat besok, apakah saya juga harus hadir?', 'Do I have to attend tomorrow\'s meeting too?'),
        (P, '{部長|ぶちょう}は {全員|ぜんいん} {出席|しゅっせき}しなくては ならないと {言|い}って いましたよ。', 'Kata kepala bagian, semua orang harus hadir.', 'The department head said everyone must attend.'),
        (Y, '{資料|しりょう}は {準備|じゅんび}しなくても かまいませんか。', 'Apakah materi tidak perlu disiapkan?', 'Is it all right if I do not prepare materials?'),
        (P, 'ええ、{資料|しりょう}は {私|わたし}が {用意|ようい}します。', 'Ya, materi saya yang siapkan.', 'Yes, I will prepare the materials.'),
        (Y, 'では、{発表|はっぴょう}の {練習|れんしゅう}を しなくては いけませんね。', 'Kalau begitu, saya harus berlatih presentasi.', 'Then I will have to practice the presentation.'),
        (P, 'ええ、がんばって ください。', 'Ya, semangat.', 'Yes, do your best.')]),
    scenario(2, 'Aku harus pergi sekarang', 'I have to go now',
             'Sore hari. Kamu pamit kepada teman dekat dengan gaya santai.',
             'Late afternoon. You say goodbye to a close friend casually.',
             'Bicara santai dengan なくちゃ／なきゃ.', 'Speak casually with nakucha / nakya.',
             ['Vなくちゃ', 'Vなきゃ いけない'], '佐藤', 'male', [
        (Y, 'そろそろ {行|い}かなくちゃ。', 'Sudah waktunya aku pergi.', 'I had better get going.'),
        (P, 'えっ、もう？', 'Eh, sudah?', 'What, already?'),
        (Y, 'うん、{五時|ごじ}までに {銀行|ぎんこう}に {行|い}かなきゃ いけないの。', 'Iya, aku harus ke bank sebelum jam lima.', 'Yes, I have to get to the bank by five.'),
        (P, '{大変|たいへん}だね。{急|いそ}がなくても、まだ {間|ま}に {合|あ}うよ。', 'Repot ya. Tidak usah buru-buru, masih sempat.', 'That is tough. You do not need to rush, you can still make it.'),
        (Y, 'ううん、{道|みち}が {込|こ}んで いるから、{早|はや}く {出|で}なくちゃ。', 'Tidak, jalanan macet, jadi harus cepat berangkat.', 'No, the roads are crowded, so I have to leave early.'),
        (P, 'じゃ、また {今度|こんど}ね。', 'Sampai jumpa lain kali.', 'See you next time, then.')]),
    scenario(3, 'Cara mengisi formulir', 'How to fill in the form',
             'Di loket. Kamu bertanya cara mengisi formulir.',
             'At a counter. You ask how to fill out a form.',
             'Menyatakan "cukup ~ saja" dengan Vるだけで いい.', 'Say "just ~ is enough" with V-ru dake de ii.',
             ['Vるだけだ', 'Nだけで いい'], '山田', 'female', [
        (Y, 'この {書類|しょるい}、どう {書|か}けば いいですか。', 'Dokumen ini bagaimana menulisnya?', 'How should I fill in this form?'),
        (P, '{名前|なまえ}と {住所|じゅうしょ}を {書|か}くだけで いいですよ。', 'Cukup tulis nama dan alamat.', 'You only need to write your name and address.'),
        (Y, 'ほかの {欄|らん}は？', 'Kolom lainnya?', 'What about the other fields?'),
        (P, '{空|あ}けて おくだけで {大丈夫|だいじょうぶ}です。', 'Cukup dikosongkan.', 'Just leaving them blank is fine.'),
        (Y, 'それだけで いいんですね。ありがとうございます。', 'Cukup itu saja ya. Terima kasih.', 'That is all? Thank you.'),
        (P, 'はい、{出|だ}すだけです。', 'Ya, tinggal diserahkan.', 'Yes, you just hand it in.')]),
    scenario(4, 'Hadiah untuk ibu dan perintah atasan', 'A gift for Mom and the boss\'s order',
             'Di kantor. Kamu bergumam soal hadiah, lalu membicarakan perintah atasan.',
             'At the office. You mutter about a gift, then mention the boss\'s order.',
             'Memakai …かな dan Vて くれ (perintah yang dikutip).', 'Use ...kana and V-te kure (quoted command).',
             ['…かな', 'Vて くれ'], '木村', 'male', [
        (Y, '{母|はは}の {誕生日|たんじょうび}に {何|なに}を あげようかな。', 'Hadiah apa ya untuk ulang tahun ibu?', 'I wonder what to give Mom for her birthday.'),
        (P, '{花|はな}は どうかな。', 'Bunga bagaimana?', 'How about flowers?'),
        (Y, 'いいね。あ、{花屋|はなや}まで {一緒|いっしょ}に {来|き}て くれないかな。', 'Bagus. Ah, bisa ikut ke toko bunga?', 'Good idea. Oh, could you come with me to the flower shop?'),
        (P, 'いいよ。そういえば、{部長|ぶちょう}は {何|なん}て {言|い}って いた？', 'Boleh. Omong-omong, kepala bagian bilang apa?', 'Sure. By the way, what did the department head say?'),
        (Y, '{明日|あした}の {資料|しりょう}を {今日中|きょうじゅう}に コピーして くれと {言|い}われた。', 'Saya disuruh menyalin materi besok hari ini juga.', 'I was told to copy tomorrow\'s materials by the end of today.'),
        (P, '{大変|たいへん}だね。', 'Repot ya.', 'That is tough.')]),
    scenario(5, 'Merendah dan terkejut', 'Modesty and surprise',
             'Istirahat siang. Rekan memuji lukisanmu lalu membawa kabar mengejutkan.',
             'Lunch break. A colleague praises your painting, then shares surprising news.',
             'Merendah dengan Nなんか dan menyatakan kaget dengan …なんて.', 'Be modest with N nanka and show surprise with ...nante.',
             ['Nなんか', '…なんて'], '高橋', 'female', [
        (P, '{田中|たなか}さんの {絵|え}、とても {上手|じょうず}ですね。', 'Lukisan Tanaka bagus sekali.', 'Your painting is very good, Tanaka.'),
        (Y, 'いえ、{私|わたし}の {絵|え}なんか、まだまだです。', 'Ah tidak, lukisan saya sih masih jauh.', 'Oh no, mine is still nowhere near good.'),
        (P, '{展覧会|てんらんかい}に {出|だ}して みませんか。', 'Mau coba ikut pameran?', 'Why not enter it in an exhibition?'),
        (Y, '{私|わたし}なんかが {出|だ}しても いいんでしょうか。', 'Apakah orang seperti saya boleh ikut?', 'Is it all right for someone like me to enter?'),
        (P, '{佐藤|さとう}さんが {来月|らいげつ} {結婚|けっこん}するそうですよ。', 'Katanya Sato menikah bulan depan.', 'I hear Sato is getting married next month.'),
        (Y, 'えっ、{結婚|けっこん}するなんて {全然|ぜんぜん} {知|し}りませんでした。', 'Eh, saya sama sekali tidak tahu dia akan menikah.', 'What, I had no idea he was getting married.')]),
    scenario(6, 'Film dan rencana ke Hokkaido', 'A movie and a Hokkaido plan',
             'Hari Senin. Rekan kerja membahas film dan rencana liburanmu.',
             'Monday. A colleague talks about a movie and your travel plans.',
             'Memakai させられる dan …なら.', 'Use saserareru and ...nara.',
             ['Vi(さ)せる', 'Vi(さ)せられる', '…なら'], '鈴木', 'male', [
        (P, '{昨日|きのう}の {映画|えいが}は どうでしたか。', 'Bagaimana film kemarin?', 'How was yesterday\'s movie?'),
        (Y, '{最後|さいご}の {場面|ばめん}に とても {感動|かんどう}させられました。', 'Saya sangat tersentuh oleh adegan terakhir.', 'I was deeply moved by the last scene.'),
        (P, '{私|わたし}は {泣|な}かされましたよ。', 'Saya sampai menangis.', 'It made me cry.'),
        (Y, '{来月|らいげつ}、{北海道|ほっかいどう}へ {旅行|りょこう}に {行|い}く {予定|よてい}なんです。', 'Bulan depan, saya berencana ke Hokkaido.', 'I am planning a trip to Hokkaido next month.'),
        (P, '{北海道|ほっかいどう}なら、{海|うみ}の {幸|さち}が おいしいですよ。{母|はは}を {喜|よろこ}ばせる お{土産|みやげ}も {買|か}えます。', 'Kalau Hokkaido, hasil lautnya enak, dan oleh-oleh yang menyenangkan ibu juga ada.', 'If it is Hokkaido, the seafood is great, and you can buy souvenirs that please your mother.'),
        (Y, 'いいですね。{食|た}べるなら、{市場|いちば}の {食堂|しょくどう}に {行|い}って みます。', 'Bagus. Kalau makan, saya akan coba kedai di pasar.', 'Sounds good. For eating, I will try the canteen at the market.')]),
]
kaiwa = {'id': 7, 'level': 'N3',
         'title_id': 'Pelajaran 7 (N3): Kewajiban, Undangan, dan Perasaan',
         'title_en': 'N3 Lesson 7: Obligations, Invitations and Feelings',
         'scenarios': scenarios}
dump('public/data/kaiwa/lesson-n3-7.json', kaiwa)
dump('public/data/kaiwa/index-n3.json', {'lessons': [
    {'id': n, 'file': f'lesson-n3-{n}.json', 'track': 'lesson', 'level': 'N3'} for n in (1, 2, 3, 4, 5, 6, 7)]})
print('ok', len(questions), len(scenarios))
