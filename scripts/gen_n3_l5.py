#!/usr/bin/env python3
"""Pembuat berkas N3 Pelajaran 5 (chokai + kaiwa) dari penanda {漢字|かな}.
Jalankan dari root project:  python3 scripts/gen_n3_l5.py
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
L = 'n3l5'
AUD = '/audio/chokai/lesson-n3-5'
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
    q(1, [(F, '先週 行った あの レストラン、おいしかったね。'),
          (Mx, 'うん、あそこの パスタは 最高だった。'),
          (F, '今度は 友達も 連れて 行こうよ。')],
      '二人《ふたり》は 何《なに》に ついて 話《はな》して いますか。',
      ['{来週|らいしゅう}の {旅行|りょこう}', '{新|あたら}しい {映画|えいが}', '{先週|せんしゅう} {行|い}った レストラン'], 2,
      'Apa yang mereka bicarakan? — Restoran yang didatangi minggu lalu.',
      'What are they talking about? — The restaurant they went to last week.'),
    q(2, [(Mx, '元気が ないね。何か あったんじゃない？'),
          (F, 'うん、実は 仕事が 忙しくて。'),
          (Mx, '少し 休んだ ほうが いいんじゃない？')],
      '男《おとこ》の 人《ひと》は 女《おんな》の 人《ひと》に 何《なん》と 言《い》いましたか。',
      ['{少|すこ}し {休|やす}んだ ほうが いい', 'もっと {働|はたら}いた ほうが いい', '{家|いえ}に {帰|かえ}らない ほうが いい'], 0,
      'Apa kata laki-laki itu? — Sebaiknya istirahat sebentar.',
      'What did the man say? — That she should rest a little.'),
    q(3, [(Mx, 'すみません、郵便局は どこですか。'),
          (F, 'あの 信号を 右へ 曲がった ところに ありますよ。'),
          (Mx, '駅から 近いですか。'),
          (F, 'ええ、歩いて 五分ぐらいです。')],
      '郵便局《ゆうびんきょく》は どこに ありますか。',
      ['{駅|えき}の {中|なか}', '{信号|しんごう}を {右|みぎ}へ {曲|ま}がった ところ', '{信号|しんごう}の {左|ひだり}'], 1,
      'Di mana kantor pos? — Di tempat setelah belok kanan di lampu.',
      'Where is the post office? — Just after turning right at the light.'),
    q(4, [(F, 'もしもし、今 どこ？'),
          (Mx, 'ごめん。ちょうど 家を 出ようと した とき、電話が かかって きたんだ。'),
          (F, '誰から？'),
          (Mx, '母から。もう すぐ 着くよ。')],
      '男《おとこ》の 人《ひと》は どうして 遅《おく》れましたか。',
      ['{電車|でんしゃ}が {止|と}まったからです', '{寝坊|ねぼう}したからです', '{電話|でんわ}が かかって きたからです'], 2,
      'Mengapa laki-laki itu terlambat? — Karena ada telepon masuk.',
      'Why was the man late? — A phone call came in.'),
    q(5, [(Mx, '来年の 景気は どうなると 思いますか。'),
          (F, '少しずつ 良く なるだろうと 思います。'),
          (Mx, '物価は？'),
          (F, '上がる だろうと 思いますが、給料も 上がるでしょう。')],
      '女《おんな》の 人《ひと》は 物価《ぶっか》は どうなると 思《おも》って いますか。',
      ['{下|さ}がる', '{上|あ}がる', '{変|か}わらない'], 1,
      'Bagaimana harga barang menurut perempuan itu? — Akan naik.',
      'What does the woman think will happen to prices? — They will rise.'),
    q(6, [(F, '十分ぐらいで 戻って きますから、ここで 待って いて くれますか。')],
      '答《こた》えは どれですか。',
      ['いいえ、{戻|もど}りました。', 'はい、{待|ま}って いました。', 'はい、わかりました。'], 2,
      'Saya kembali sepuluh menit lagi, bisa tunggu di sini? — Ya, baik.',
      'I will be back in ten minutes; could you wait here? — Yes, all right.'),
    q(7, [(Mx, '田中さんが 作った ケーキ、どうでしたか。')],
      '答《こた》えは どれですか。',
      ['すばらしかったです。', '{田中|たなか}さんが {作|つく}りました。', 'ケーキを {買|か}います。'], 0,
      'Bagaimana kue buatan Tanaka? — Luar biasa.',
      'How was the cake Tanaka made? — It was wonderful.'),
    q(8, [(Mx, '友達との 旅行だったので、とても 楽しかったです。'),
          (F, 'お土産は 買いましたか。'),
          (Mx, 'はい、先生への お土産に チョコレートを 買いました。')],
      '男《おとこ》の 人《ひと》は 先生《せんせい》に お土産《みやげ》を 買《か》いました。', OX, 0,
      'Laki-laki itu membeli oleh-oleh untuk guru. → ○',
      'The man bought a souvenir for his teacher. → ○'),
    q(9, [(F, 'この 店、クレジットカードが 使えるのだろうか。'),
          (Mx, '店員さんに 聞いて みたら？')],
      '女《おんな》の 人《ひと》は もう 店員《てんいん》さんに 聞《き》きました。', OX, 1,
      'Perempuan itu sudah bertanya ke pelayan. → ×',
      'The woman has already asked the clerk. → ×'),
    q(10, [(Mx, '階段を 下りた ところが 出口です。'),
           (F, 'ありがとうございます。')],
      '出口《でぐち》は 階段《かいだん》を 上《あ》がった ところです。', OX, 1,
      'Pintu keluar ada di tempat setelah naik tangga. → ×',
      'The exit is where you go up the stairs. → ×'),
]

kaiwa_turns = [
    (F, 'すみません、図書館は どこですか。'),
    (Mx, 'この 道を まっすぐ 行って、大通りに 出たら 右へ 曲がって ください。'),
    (F, '大通りに 出たら、右ですね。'),
    (Mx, 'はい。横断歩道を 渡った ところに ありますよ。'),
    (F, 'ありがとうございます。'),
    (Mx, 'いいえ。'),
]
kaiwa_qs = [
    {'id': f'{L}-kq1', 'prompt': ja('大通《おおどお》りに 出《で》たら、どちらへ 曲《ま》がりますか。'),
     'choices': [ja(c) for c in ['{左|ひだり}', '{右|みぎ}', 'まっすぐ']], 'answer': 1, 'audio': None,
     'translate': {'id': 'Setelah tiba di jalan raya, belok ke mana? — Kanan.', 'en': 'Which way do you turn after reaching the main street? — Right.'}},
    {'id': f'{L}-kq2', 'prompt': ja('図書館《としょかん》は どこに ありますか。'),
     'choices': [ja(c) for c in ['{横断歩道|おうだんほどう}を {渡|わた}った ところ', '{駅|えき}の {前|まえ}', '{大通|おおどお}りの {左|ひだり}']], 'answer': 0, 'audio': None,
     'translate': {'id': 'Di mana perpustakaan? — Di tempat setelah menyeberang zebra cross.', 'en': 'Where is the library? — Just after crossing the crosswalk.'}},
]

chokai = {
    'id': 'n3-5', 'level': 'N3',
    'focus': 'N3 Pelajaran 5 — あ〜・そ〜, …んじゃない？, Vた ところに／で, Vよう(意向形)と する, …のだろうか, Nとの／での／からの／までの／への N, …だろう・…だろうと思う, …から、〜てください, が／の',
    'kaiwa': {'id': 'kaiwa',
              'audio_text': [{'speaker': s, 'text': t} for s, t in kaiwa_turns],
              'audio': '/audio/chokai/lesson-n3-5/kaiwa.mp3',
              'questions': kaiwa_qs},
    'questions': questions,
}
dump('public/data/chokai/lesson-n3-5.json', chokai)


def turn(sid, n, who, text, idt, ent):
    d = {'id': f'{sid}-t{n}', 'who': who, 'ja': ja(text), 'speak': speak(text),
         'id_text': idt, 'en_text': ent, 'audio': f'/audio/kaiwa/lesson-n3-5/{sid}-t{n}.mp3'}
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
    scenario(1, 'Restoran yang sama-sama kita kenal', 'A restaurant we both know',
             'Istirahat siang. Kamu dan rekan membahas restoran yang pernah didatangi bersama.',
             'Lunch break. You and a colleague talk about a restaurant you visited together.',
             'Memakai あの／あそこ dan その untuk hal yang sama-sama dikenal dan yang baru disebut.', 'Use ano / asoko and sono for shared and just-mentioned things.',
             ['あ〜・そ〜'], '中村', 'female', [
        (P, '{先週|せんしゅう} {行|い}った あの レストラン、おいしかったですね。', 'Restoran yang kita datangi minggu lalu enak ya.', 'The restaurant we went to last week was good.'),
        (Y, 'ええ、あそこの パスタは {最高|さいこう}でした。', 'Ya, pasta di sana luar biasa.', 'Yes, the pasta there was the best.'),
        (P, '{昨日|きのう}、{駅|えき}で ミラーさんという {人|ひと}に {会|あ}ったんですが、その {人|ひと}も あの {店|みせ}が {好|す}きなんですって。', 'Kemarin saya bertemu Miller di stasiun, katanya orang itu juga suka toko itu.', 'I met a person called Miller at the station yesterday, and he likes that shop too.'),
        (Y, 'そうですか。また {一緒|いっしょ}に {行|い}きましょう。', 'Begitu. Ayo pergi lagi bersama.', 'I see. Let us go together again.'),
        (P, '{来週|らいしゅう}は どうですか。', 'Minggu depan bagaimana?', 'How about next week?'),
        (Y, 'いいですね。その {日|ひ}は {大丈夫|だいじょうぶ}です。', 'Boleh. Hari itu saya bisa.', 'Sounds good. I am free that day.')]),
    scenario(2, 'Kamu kelihatan lelah', 'You look tired',
             'Sore di kantor. Teman dekat menyadari kamu kurang bersemangat.',
             'Late afternoon at the office. A close friend notices you are down.',
             'Menyatakan dugaan akrab dengan …んじゃない？.', 'Voice a casual guess with ...n ja nai?',
             ['…んじゃない？'], '佐藤', 'male', [
        (P, '{元気|げんき}が ないね。{何|なに}か あったんじゃない？', 'Kamu lesu ya. Ada apa-apa, kan?', 'You look down. Did something happen?'),
        (Y, 'うん、{実|じつ}は… {仕事|しごと}が {忙|いそが}しくて。', 'Iya, sebenarnya... pekerjaan sibuk sekali.', 'Yeah, actually... work has been busy.'),
        (P, '{少|すこ}し {休|やす}んだ ほうが いいんじゃない？', 'Sebaiknya istirahat sebentar, kan?', 'Maybe you should take a little rest?'),
        (Y, 'そうだね。{今週末|こんしゅうまつ}は {休|やす}むよ。', 'Benar juga. Akhir pekan ini saya istirahat.', 'You are right. I will rest this weekend.'),
        (P, 'それが いいよ。{無理|むり}しないでね。', 'Bagus itu. Jangan memaksakan diri ya.', 'That is good. Do not overdo it.'),
        (Y, 'ありがとう。', 'Terima kasih.', 'Thanks.')]),
    scenario(3, 'Menanyakan jalan ke kantor pos', 'Asking the way to the post office',
             'Di jalan dekat stasiun. Kamu bertanya arah kepada orang yang lewat.',
             'On a street near the station. You ask a passer-by for directions.',
             'Menunjukkan lokasi dengan Vた ところ dan permintaan 〜から、〜てください.', 'Locate places with V-ta tokoro and requests with ~kara ~te kudasai.',
             ['Vた ところに／で', '…から、〜て ください'], '山田', 'female', [
        (Y, 'すみません、{郵便局|ゆうびんきょく}は どこですか。', 'Maaf, kantor pos di mana?', 'Excuse me, where is the post office?'),
        (P, 'あの {信号|しんごう}を {右|みぎ}へ {曲|ま}がった ところに ありますよ。', 'Ada di tempat setelah belok kanan di lampu itu.', 'It is right after you turn right at that traffic light.'),
        (Y, '{駅|えき}から {近|ちか}いですか。', 'Apakah dekat dari stasiun?', 'Is it close to the station?'),
        (P, 'ええ、{五分|ごふん}ぐらいですから、{歩|ある}いて {行|い}って ください。', 'Ya, sekitar lima menit, jadi berjalan kakilah.', 'Yes, it is about five minutes, so please walk.'),
        (Y, 'わかりました。{信号|しんごう}を {右|みぎ}ですね。', 'Baik. Lampu itu, belok kanan ya.', 'Understood. Right at the light, correct?'),
        (P, 'はい、{気|き}を つけて。', 'Ya, hati-hati.', 'Yes, take care.')]),
    scenario(4, 'Saat hendak berangkat, telepon masuk', 'A call just as you were leaving',
             'Di telepon. Teman menunggu dan bertanya kamu di mana.',
             'On the phone. A friend is waiting and asks where you are.',
             'Menjelaskan alasan terlambat dengan Vようと した とき.', 'Explain being late with V-you to shita toki.',
             ['Vよう(意向形)と する'], '木村', 'male', [
        (P, 'もしもし、{今|いま} どこ？', 'Halo, kamu di mana sekarang?', 'Hello, where are you now?'),
        (Y, 'ごめん。ちょうど {家|いえ}を {出|で}ようと した とき、{電話|でんわ}が かかって きたんだ。', 'Maaf. Pas mau keluar rumah, ada telepon masuk.', 'Sorry. Just as I was about to leave home, a call came in.'),
        (P, '{誰|だれ}から？', 'Dari siapa?', 'From whom?'),
        (Y, '{母|はは}から。もう すぐ {着|つ}くよ。', 'Dari ibu. Sebentar lagi sampai.', 'From my mother. I will be there soon.'),
        (P, 'わかった。{先|さき}に {入|はい}って いるね。', 'Oke. Aku masuk duluan ya.', 'Okay. I will go in first.'),
        (Y, 'うん、すぐ {行|い}く。', 'Iya, aku segera datang.', 'Yes, I will be right there.')]),
    scenario(5, 'Cerita perjalanan ke Hokkaido', 'A trip to Hokkaido',
             'Hari Senin di kantor. Rekan menanyakan perjalanan akhir pekan lalu.',
             'Monday at the office. A colleague asks about last week\'s trip.',
             'Memakai Nとの／への N dan が／の dalam anak kalimat penerang kata benda.', 'Use N-tono / N-eno N and ga / no inside noun-modifying clauses.',
             ['Nとの／への N', 'が／の'], '高橋', 'female', [
        (P, '{先週|せんしゅう}の {北海道|ほっかいどう}{旅行|りょこう}は どうでしたか。', 'Bagaimana perjalanan ke Hokkaido minggu lalu?', 'How was last week\'s trip to Hokkaido?'),
        (Y, '{友達|ともだち}との {旅行|りょこう}だったので、とても {楽|たの}しかったです。', 'Karena bersama teman, sangat menyenangkan.', 'It was a trip with friends, so it was very fun.'),
        (P, 'お{土産|みやげ}は {買|か}いましたか。', 'Beli oleh-oleh?', 'Did you buy souvenirs?'),
        (Y, 'はい、{先生|せんせい}への お{土産|みやげ}に チョコレートを {買|か}いました。', 'Ya, saya membeli cokelat sebagai oleh-oleh untuk guru.', 'Yes, I bought chocolate as a souvenir for my teacher.'),
        (P, '{田中|たなか}さんの {作|つく}った ケーキも ありましたか。', 'Ada juga kue buatan Tanaka?', 'Was there cake that Tanaka made too?'),
        (Y, 'はい、{田中|たなか}さんが {作|つく}った ケーキも {食|た}べました。', 'Ya, kue yang dibuat Tanaka juga saya makan.', 'Yes, I also ate the cake Tanaka made.')]),
    scenario(6, 'Dugaan tentang ekonomi', 'Guessing about the economy',
             'Istirahat siang. Rekan senior bertanya pendapatmu tentang ekonomi.',
             'Lunch break. A senior asks your opinion on the economy.',
             'Menyatakan dugaan dengan …だろうと 思う dan …のだろうか.', 'Voice conjecture with ...darou to omou and ...no darou ka.',
             ['…だろう・…だろうと 思う', '…のだろうか'], '鈴木', 'male', [
        (P, '{来年|らいねん}の {景気|けいき}は どうなると {思|おも}いますか。', 'Menurutmu ekonomi tahun depan akan bagaimana?', 'What do you think will happen to the economy next year?'),
        (Y, '{少|すこ}しずつ {良|よ}く なるだろうと {思|おも}います。', 'Saya kira perlahan membaik.', 'I think it will improve little by little.'),
        (P, '{物価|ぶっか}は？', 'Kalau harga barang?', 'And prices?'),
        (Y, '{上|あ}がるだろうと {思|おも}いますが、{給料|きゅうりょう}も {上|あ}がるでしょう。', 'Saya kira naik, tapi gaji juga akan naik.', 'I think they will rise, but salaries will rise too.'),
        (P, '{本当|ほんとう}に そう なるのだろうか。', 'Apa benar akan begitu, ya?', 'I wonder if that will really happen.'),
        (Y, 'どう なるのだろうかと {心配|しんぱい}ですが、{待|ま}って みましょう。', 'Saya juga khawatir akan jadi bagaimana, tapi mari kita tunggu.', 'I also wonder how it will turn out, but let us wait and see.')]),
]
kaiwa = {'id': 5, 'level': 'N3',
         'title_id': 'Pelajaran 5 (N3): Petunjuk Jalan dan Dugaan',
         'title_en': 'N3 Lesson 5: Directions and Conjecture',
         'scenarios': scenarios}
dump('public/data/kaiwa/lesson-n3-5.json', kaiwa)
dump('public/data/kaiwa/index-n3.json', {'lessons': [
    {'id': n, 'file': f'lesson-n3-{n}.json', 'track': 'lesson', 'level': 'N3'} for n in (1, 2, 3, 4, 5)]})
print('ok', len(questions), len(scenarios))
