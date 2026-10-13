#!/usr/bin/env python3
"""Pembuat berkas N3 Pelajaran 1 (chokai + kaiwa) dari penanda {漢字|かな}.
Jalankan dari root project:  python3 scripts/gen_n3_l1.py
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


# ------------------------------------------------------------------ CHOKAI
F, Mx = 'female', 'male'
L = 'n3l1'
AUD = '/audio/chokai/lesson-n3-1'


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


OX = ['○', '×']
questions = [
    # A. Dialog pendek, pertanyaan isi
    q(1, [(F, 'すみません、この 荷物を 二階まで 運んで いただけませんか。'),
          (Mx, 'ええ、いいですよ。この 箱だけですか。'),
          (F, 'いいえ、あそこの 青い かばんも お願いします。')],
      '男《おとこ》の 人《ひと》は 何《なに》を 運《はこ》びますか。',
      ['{箱|はこ}だけ', '{箱|はこ}と {青|あお}い かばん', '{青|あお}い かばんだけ'], 1,
      'Apa yang akan dibawa laki-laki itu? — Kotak dan tas biru.',
      'What will the man carry? — The box and the blue bag.'),
    q(2, [(Mx, '新しい 事務所は どうですか。'),
          (F, '広くて、ホテルのようですよ。でも、机が 少し 古いんです。')],
      '新《あたら》しい 事務所《じむしょ》は どんな 事務所《じむしょ》ですか。',
      ['{小|ちい}さくて {古|ふる}いです', '{机|つくえ}が {新|あたら}しいです', 'ホテルのように {広|ひろ}いです'], 2,
      'Kantor baru itu seperti apa? — Luas seperti hotel.',
      'What is the new office like? — Spacious, like a hotel.'),
    q(3, [(Mx, 'ご趣味は 何ですか。'),
          (F, '散歩しながら 写真を 撮る ことです。'),
          (Mx, 'いいですね。私は 料理を 作る ことが 好きなんです。')],
      '女《おんな》の 人《ひと》の 趣味《しゅみ》は 何《なん》ですか。',
      ['{料理|りょうり}を {作|つく}る こと', '{写真|しゃしん}を {撮|と}る こと', '{散歩|さんぽ}の {本|ほん}を {読|よ}む こと'], 1,
      'Apa hobi perempuan itu? — Memotret.',
      'What is the woman\'s hobby? — Taking photos.'),
    q(4, [(F, '昨日、田中という 人が 来ましたよ。'),
          (Mx, 'どこの 会社の 人ですか。'),
          (F, '青空商事という 会社の 方だそうです。')],
      '田中《たなか》さんは どこの 人《ひと》ですか。',
      ['{大空|おおぞら}{商事|しょうじ}', '{青空|あおぞら}{商事|しょうじ}', '{田中|たなか}{商事|しょうじ}'], 1,
      'Pak Tanaka dari mana? — Dari Aozora Shoji.',
      'Where is Mr. Tanaka from? — Aozora Shoji.'),
    q(5, [(F, '明日は 雨が 降っても、試合を しますか。'),
          (Mx, 'はい、どんなに 寒くても やります。でも、雪が たくさん 降ったら 中止です。')],
      '明日《あした》、雨《あめ》が 降《ふ》ったら 試合《しあい》は どうなりますか。',
      ['{中止|ちゅうし}に なります', '{来週|らいしゅう}に なります', '{試合|しあい}を します'], 2,
      'Jika besok hujan, pertandingannya bagaimana? — Tetap diadakan.',
      'If it rains tomorrow, what happens to the match? — It is held.'),
    # B. Pilih tanggapan
    q(6, [(Mx, 'すみません、写真を 撮って もらえませんか。')],
      '答《こた》えは どれですか。',
      ['はい、{撮|と}って もらいます。', 'いいえ、{撮|と}りました。', 'はい、いいですよ。'], 2,
      'Maukah Anda memotretkan saya? — Ya, boleh.',
      'Could you take a photo for me? — Yes, of course.'),
    q(7, [(F, 'お正月に 食べる この 料理は 何と 言いますか。')],
      '答《こた》えは どれですか。',
      ['おせち{料理|りょうり}と {言|い}います。', 'おせち{料理|りょうり}に {言|い}いました。', 'おせち{料理|りょうり}を {言|い}います。'], 0,
      'Masakan Tahun Baru ini disebut apa? — Disebut osechi ryouri.',
      'What is this New Year food called? — It is called osechi ryouri.'),
    # C. Benar/salah
    q(8, [(Mx, '昨日の 夜は ほとんど 寝て いないのです。隣の 部屋が うるさかったのです。'),
          (F, '大変でしたね。今日は 早く 休んで ください。')],
      '男《おとこ》の 人《ひと》は 昨日《きのう》 よく 寝《ね》ました。', OX, 1,
      'Laki-laki itu tidur nyenyak semalam. → ×',
      'The man slept well last night. → ×'),
    q(9, [(F, '昨日の セールは すごかったですよ。店の 前に 人が 何十人も 並んで いました。'),
          (Mx, '買えましたか。'),
          (F, '二時間も 待って、やっと 買えました。')],
      '女《おんな》の 人《ひと》は 二時間《にじかん》 待《ま》ちました。', OX, 0,
      'Perempuan itu menunggu dua jam. → ○',
      'The woman waited two hours. → ○'),
    q(10, [(Mx, '今夜は 肉を 食べに 行きませんか。'),
           (F, '肉じゃなくて、魚が いいなあ。'),
           (Mx, 'じゃ、寿司に しましょう。')],
      '女《おんな》の 人《ひと》は 肉《にく》が 食《た》べたいです。', OX, 1,
      'Perempuan itu ingin makan daging. → ×',
      'The woman wants to eat meat. → ×'),
]

kaiwa_turns = [
    (F, 'ブディさん、いらっしゃい。どうぞ、上がって ください。'),
    (Mx, 'おじゃまします。この 部屋は 畳ですね。'),
    (F, 'ええ。座布団を 敷きますから、ここに 座って ください。'),
    (Mx, '正座は ちょっと 苦手なんですが、楽に しても いいですか。'),
    (F, 'もちろん。足を 伸ばしても いいですよ。'),
    (Mx, 'ありがとうございます。畳の においが いいですね。'),
]
kaiwa_qs = [
    {'id': f'{L}-kq1', 'prompt': ja('二人《ふたり》は どこに いますか。'),
     'choices': [ja(c) for c in ['{畳|たたみ}の {部屋|へや}', '{会社|かいしゃ}', '{病院|びょういん}']], 'answer': 0, 'audio': None,
     'translate': {'id': 'Di mana mereka berada? — Di ruangan bertatami.', 'en': 'Where are they? — In a tatami room.'}},
    {'id': f'{L}-kq2', 'prompt': ja('男《おとこ》の 人《ひと》は どうして 楽《らく》に しても いいか 聞《き》きましたか。'),
     'choices': [ja(c) for c in ['{正座|せいざ}が {苦手|にがて}だからです', '{座布団|ざぶとん}が ないからです', '{畳|たたみ}が {古|ふる}いからです']], 'answer': 0, 'audio': None,
     'translate': {'id': 'Mengapa ia bertanya apakah boleh duduk santai? — Karena kurang bisa duduk bersimpuh.',
                   'en': 'Why did he ask if he could relax? — Because he is not good at kneeling formally.'}},
]

chokai = {
    'id': 'n3-1',
    'level': 'N3',
    'focus': 'N3 Pelajaran 1 — Vて もらえませんか／いただけませんか, Nの ようだ／ような／ように, Vる こと, N１を N２と 言う, N１という N２, 〜ても, N１じゃなくて N２, 〜のだ, 何人も・何回も・何枚も',
    'kaiwa': {
        'id': 'kaiwa',
        'audio_text': [{'speaker': s, 'text': t} for s, t in kaiwa_turns],
        'audio': '/audio/chokai/lesson-n3-1/kaiwa.mp3',
        'questions': kaiwa_qs,
    },
    'questions': questions,
}
dump('public/data/chokai/lesson-n3-1.json', chokai)

# ------------------------------------------------------------------ KAIWA
def turn(sid, n, who, text, idt, ent, extra=()):
    d = {
        'id': f'{sid}-t{n}',
        'who': who,
        'ja': ja(text),
        'speak': speak(text),
        'id_text': idt,
        'en_text': ent,
        'audio': f'/audio/kaiwa/lesson-n3-1/{sid}-t{n}.mp3',
    }
    if who == 'you':
        acc = []
        for a in [plain(text), plain(text).replace(' ', ''), speak(text), speak(text).replace(' ', '')] + list(extra):
            if a not in acc:
                acc.append(a)
        d['accept'] = acc
    return d


def scenario(k, title_id, title_en, setting_id, setting_en, goal_id, goal_en, patterns, partner, voice, rows):
    sid = f'{L}-s{k}'
    return {
        'id': sid, 'title_id': title_id, 'title_en': title_en,
        'setting_id': setting_id, 'setting_en': setting_en,
        'goal_id': goal_id, 'goal_en': goal_en,
        'patterns': patterns,
        'partner': {'name': partner, 'voice': voice},
        'you': {'name': 'ブディ'},
        'turns': [turn(sid, i, *r) for i, r in enumerate(rows, 1)],
    }


P, Y = 'partner', 'you'
scenarios = [
    scenario(1, 'Meminta bantuan dengan sangat sopan', 'Asking for help very politely',
             'Di kantor. Kamu perlu memindahkan kardus dan meminta bantuan senior.',
             'At the office. You need to move boxes and ask a senior colleague for help.',
             'Meminta tolong dengan 〜て いただけませんか.', 'Ask politely with ~te itadakemasen ka.',
             ['Vて もらえませんか', 'Vて いただけませんか'], '中村', 'female', [
        (P, 'ブディさん、どうしたんですか。', 'Budi, ada apa?', 'Budi, what is the matter?'),
        (Y, 'すみません、この {荷物|にもつ}を {二階|にかい}まで {運|はこ}んで いただけませんか。', 'Maaf, bisakah Anda membawa barang ini sampai lantai dua?', 'Excuse me, could you carry this luggage up to the second floor?'),
        (P, 'いいですよ。{何|なに}を すれば いいですか。', 'Boleh. Apa yang harus saya lakukan?', 'Sure. What should I do?'),
        (Y, 'この {箱|はこ}を {持|も}って いただけませんか。', 'Bisakah Anda memegang kotak ini?', 'Could you hold this box?'),
        (P, 'わかりました。ほかにも ありますか。', 'Baik. Apakah ada yang lain juga?', 'All right. Is there anything else?'),
        (Y, 'いいえ、これだけです。ありがとうございます。', 'Tidak, hanya ini. Terima kasih.', 'No, that is all. Thank you.')]),
    scenario(2, 'Kantor baru seperti hotel', 'A new office like a hotel',
             'Siang hari. Teman kantor bertanya kesan kamu tentang kantor baru.',
             'Daytime. A colleague asks what you think of the new office.',
             'Membuat perumpamaan dengan Nの ようです／ような／ように.', 'Make comparisons with N no you desu / you na / you ni.',
             ['Nの ようだ', 'Nの ような N', 'Nの ように V'], '佐藤', 'male', [
        (P, '{新|あたら}しい {事務所|じむしょ}は どうですか。', 'Bagaimana kantor yang baru?', 'How is the new office?'),
        (Y, '{広|ひろ}くて、まるで ホテルのようです。', 'Luas, persis seperti hotel.', 'It is spacious, just like a hotel.'),
        (P, '{社長|しゃちょう}の {部屋|へや}も {見|み}ましたか。', 'Apakah Anda sudah melihat ruang direktur juga?', 'Did you see the president\'s room too?'),
        (Y, 'はい、{美術館|びじゅつかん}のような {雰囲気|ふんいき}でした。', 'Ya, suasananya seperti museum.', 'Yes, it had an atmosphere like a museum.'),
        (P, 'みんな {夢|ゆめ}のように {喜|よろこ}んで いますよ。', 'Semua orang senang sekali, seperti dalam mimpi.', 'Everyone is delighted, as if in a dream.'),
        (Y, '{私|わたし}も うれしいです。', 'Saya juga senang.', 'I am happy too.')]),
    scenario(3, 'Hobi: memotret dan memasak', 'Hobbies: photography and cooking',
             'Istirahat makan siang. Rekan kerja menanyakan hobi kamu.',
             'Lunch break. A colleague asks about your hobbies.',
             'Menyebut hobi dengan Vる ことが 好きです／ことです.', 'State hobbies with V-ru koto ga suki desu / koto desu.',
             ['Vる ことです', 'Vる ことが 好きだ', 'Vる ことは 〜'], '山田', 'female', [
        (P, 'ブディさんの ご{趣味|しゅみ}は {何|なん}ですか。', 'Apa hobi Budi?', 'What is your hobby, Budi?'),
        (Y, '{写真|しゃしん}を {撮|と}る ことです。', 'Memotret.', 'Taking photos.'),
        (P, 'いいですね。どんな {写真|しゃしん}を {撮|と}りますか。', 'Bagus. Foto seperti apa yang Anda ambil?', 'Nice. What kind of photos do you take?'),
        (Y, '{散歩|さんぽ}しながら {撮|と}る ことが {好|す}きなんです。', 'Saya suka memotret sambil berjalan-jalan.', 'I like taking them while I walk.'),
        (P, '{私|わたし}は {料理|りょうり}を {作|つく}る ことが {好|す}きです。', 'Saya suka memasak.', 'I like cooking.'),
        (Y, '{料理|りょうり}が できる ことは すてきですね。', 'Bisa memasak itu keren, ya.', 'Being able to cook is wonderful.')]),
    scenario(4, 'Makanan Tahun Baru', 'New Year food',
             'Awal Januari di kantor. Rekan kerja membawa kotak makanan Tahun Baru.',
             'Early January at the office. A colleague brings a box of New Year food.',
             'Menyebut nama dengan Nを Nと 言う／Nという N.', 'Name things with N o N to iu / N toiu N.',
             ['N１を N２と 言う', 'N１という N２'], '鈴木', 'male', [
        (P, 'ブディさん、これを どうぞ。', 'Budi, silakan ini.', 'Budi, please have some of this.'),
        (Y, 'ありがとうございます。これは {何|なん}と {言|い}いますか。', 'Terima kasih. Ini disebut apa?', 'Thank you. What is this called?'),
        (P, 'おせち{料理|りょうり}と {言|い}います。お{正月|しょうがつ}に {食|た}べる {料理|りょうり}です。', 'Namanya osechi ryouri. Masakan yang dimakan saat Tahun Baru.', 'It is called osechi ryouri. It is food eaten at New Year.'),
        (Y, 'この {箱|はこ}も {名前|なまえ}が あるんですか。', 'Apakah kotak ini juga punya nama?', 'Does this box have a name too?'),
        (P, 'ええ、お{重|じゅう}という {箱|はこ}です。', 'Ya, kotak yang bernama ojuu.', 'Yes, it is a box called ojuu.'),
        (Y, 'そうですか。{勉強|べんきょう}に なりました。', 'Begitu. Saya jadi belajar sesuatu.', 'I see. I learned something.')]),
    scenario(5, 'Pertandingan tetap jalan meski hujan', 'The match goes on even in rain',
             'Hari Jumat sore. Kamu bertanya soal pertandingan sepak bola perusahaan besok.',
             'Friday afternoon. You ask about tomorrow\'s company soccer match.',
             'Mengatakan "meskipun" dengan 〜ても.', 'Say "even if" with ~temo.',
             ['〜ても', 'どんなに 〜ても', 'いつ／どこ／何 〜ても'], '木村', 'male', [
        (Y, '{明日|あした}は {雨|あめ}が {降|ふ}っても、{試合|しあい}が ありますか。', 'Besok, meski hujan, apakah pertandingan tetap ada?', 'Will the match go ahead tomorrow even if it rains?'),
        (P, 'ええ、{雨|あめ}が {降|ふ}っても {行|おこな}います。', 'Ya, meski hujan tetap diadakan.', 'Yes, it is held even if it rains.'),
        (Y, 'どんなに {寒|さむ}くても ですか。', 'Sedingin apa pun?', 'No matter how cold?'),
        (P, 'はい、{寒|さむ}くても {中止|ちゅうし}には なりません。', 'Ya, sedingin apa pun tidak dibatalkan.', 'Yes, it will not be cancelled even if it is cold.'),
        (Y, 'わかりました。だれが {来|き}ても いいですか。', 'Baik. Siapa pun boleh datang?', 'Understood. Can anyone come?'),
        (P, 'もちろん、だれでも {大歓迎|だいかんげい}ですよ。', 'Tentu saja, siapa pun sangat diterima.', 'Of course, everyone is welcome.')]),
    scenario(6, 'Bukan daging, tapi ikan', 'Not meat, but fish',
             'Malam hari. Kamu dan teman kantor memilih tempat makan.',
             'Evening. You and a colleague choose a place to eat.',
             'Mengoreksi dengan N１じゃなくて N２ dan menjelaskan alasan dengan 〜のです.', 'Correct with N1 janakute N2 and explain with ~no desu.',
             ['N１じゃなくて、N２', '〜のです／〜のではありません'], '高橋', 'female', [
        (P, '{今夜|こんや}は {肉|にく}を {食|た}べに {行|い}きませんか。', 'Malam ini mau makan daging?', 'Shall we go for meat tonight?'),
        (Y, '{肉|にく}じゃなくて、{魚|さかな}が いいです。', 'Bukan daging, saya mau ikan.', 'Not meat, I would like fish.'),
        (P, 'どうして ですか。', 'Kenapa?', 'Why?'),
        (Y, '{昨日|きのう}も {肉|にく}を {食|た}べたのです。', 'Kemarin saya juga makan daging.', 'I ate meat yesterday too.'),
        (P, 'じゃ、{寿司|すし}に しましょうか。', 'Kalau begitu, bagaimana kalau sushi?', 'Then how about sushi?'),
        (Y, 'はい、{回|まわ}る {寿司|ずし}じゃなくて、カウンターの お{店|みせ}が いいです。', 'Ya, bukan sushi berputar, tapi kedai yang ada meja konternya.', 'Yes, not conveyor-belt sushi, but a counter place.')]),
]
kaiwa = {
    'id': 1, 'level': 'N3',
    'title_id': 'Pelajaran 1 (N3): Rumah, Hobi, dan Permintaan Sopan',
    'title_en': 'N3 Lesson 1: Home, Hobbies and Polite Requests',
    'scenarios': scenarios,
}
dump('public/data/kaiwa/lesson-n3-1.json', kaiwa)
dump('public/data/kaiwa/index-n3.json', {'lessons': [{'id': 1, 'file': 'lesson-n3-1.json', 'track': 'lesson', 'level': 'N3'}]})
print('ok', len(questions), len(scenarios))
