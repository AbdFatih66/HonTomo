#!/usr/bin/env python3
"""Pembuat berkas N3 Pelajaran 10 (chokai + kaiwa) dari penanda {漢字|かな}.
Jalankan dari root project:  python3 scripts/gen_n3_l10.py
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
L = 'n3l10'
AUD = '/audio/chokai/lesson-n3-10'
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
    q(1, [(Mx, '山田さん、まだ 来ないね。'),
          (F, '十時の 電車に 乗ると 言って いたから、もうすぐ 着く はずだよ。'),
          (Mx, '電話して みようか。'),
          (F, '移動中の はずだから、少し 待とう。')],
      '二人《ふたり》は これから どう しますか。',
      ['{電話|でんわ}する', '{先|さき}に {行|い}く', '{少|すこ}し {待|ま}つ'], 2,
      'Apa yang akan mereka lakukan? — Menunggu sebentar.',
      'What will they do next? — Wait a little.'),
    q(2, [(Mx, 'この 財布、鈴木さんのかな。'),
          (F, '鈴木さんは 先週から 出張中だから、会社に ある はずが ないよ。'),
          (Mx, 'そうか。じゃあ、受付に 届けよう。')],
      '財布《さいふ》を どう しますか。',
      ['{鈴木|すずき}さんに {返|かえ}す', '{受付|うけつけ}に {届|とど}ける', '{捨|す}てる'], 1,
      'Dompetnya diapakan? — Diserahkan ke resepsionis.',
      'What will they do with the wallet? — Hand it in at reception.'),
    q(3, [(F, '昨日の 試合、見に 行った？'),
          (Mx, '行く はずだったんだけど、急に 残業に なっちゃって。'),
          (F, 'それは 残念だったね。'),
          (Mx, 'うん。チケットも 買って あったのに。')],
      '男《おとこ》の 人《ひと》は どうして 試合《しあい》に 行《い》けませんでしたか。',
      ['{病気|びょうき}だったからです', 'チケットが なかったからです', '{残業|ざんぎょう}に なったからです'], 2,
      'Mengapa laki-laki itu tidak bisa ke pertandingan? — Karena tiba-tiba lembur.',
      'Why could the man not go to the match? — He suddenly had to work overtime.'),
    q(4, [(F, '新しい 機械は どう？'),
          (Mx, '便利だけど、ときどき 止まる ことが あるんだ。'),
          (F, '原因は わかった？'),
          (Mx, 'いいえ。動かない ことも あるから、メーカーに 連絡しようと 思って いる。')],
      '男《おとこ》の 人《ひと》は これから 何《なに》を しますか。',
      ['{機械|きかい}を {捨|す}てます', 'メーカーに {連絡|れんらく}します', '{説明書|せつめいしょ}を {読|よ}みます'], 1,
      'Apa yang akan dilakukan laki-laki itu? — Menghubungi pabrikan.',
      'What will the man do next? — Contact the manufacturer.'),
    q(5, [(Mx, '騒音の 調査は どう なりましたか。'),
          (F, '測定した 結果、基準を 超えて いる 場所が ありました。'),
          (Mx, '対策は？'),
          (F, '話し合いの 結果、来月 壁を 増やす ことに なりました。')],
      '来月《らいげつ》 何《なに》を しますか。',
      ['{機械|きかい}を {買|か}います', '{調査|ちょうさ}を します', '{壁|かべ}を {増|ふ}やします'], 2,
      'Apa yang dilakukan bulan depan? — Menambah dinding.',
      'What will they do next month? — Add walls.'),
    q(6, [(F, 'あっ、切符を 買い忘れちゃった。')],
      '答《こた》えは どれですか。',
      ['{切符|きっぷ}は {高|たか}いです。', '{今|いま}なら まだ {間|ま}に {合|あ}うよ。', '{忘|わす}れました。'], 1,
      'Aduh, lupa membeli tiket. — Sekarang masih sempat.',
      'Oh no, I forgot to buy a ticket. — You can still make it now.'),
    q(7, [(Mx, '山田さんは どうして 来ないんだろう。')],
      '答《こた》えは どれですか。',
      ['{電車|でんしゃ}が {遅|おく}れて いる はずだよ。', '{山田|やまだ}さんは {来|き}ました。', '{山田|やまだ}さんの {電話番号|でんわばんごう}です。'], 0,
      'Mengapa Yamada belum datang? — Mungkin keretanya terlambat.',
      'Why has Yamada not come? — His train must be delayed.'),
    q(8, [(F, '外、見て。雨が 降り出したよ。'),
          (Mx, '本当だ。傘、持って きた？'),
          (F, 'ううん、持って きて いない。')],
      '二人《ふたり》は 傘《かさ》を 持《も》って います。', OX, 1,
      'Keduanya membawa payung. → ×',
      'They both have umbrellas. → ×'),
    q(9, [(Mx, 'レポートは もう 書き終わりましたか。'),
          (F, 'いいえ、まだ 書き始めた ところです。')],
      '女《おんな》の 人《ひと》は レポートを 書《か》き始《はじ》めた ところです。', OX, 0,
      'Perempuan itu baru mulai menulis laporan. → ○',
      'The woman has just started writing the report. → ○'),
    q(10, [(F, '部長が 戻るのは 金曜日で、会議は 木曜日ですね。'),
           (Mx, 'ええ。ということは、部長は 会議に 出られないと いう ことに なりますね。')],
      '部長《ぶちょう》は 会議《かいぎ》に 出《で》られます。', OX, 1,
      'Kepala bagian bisa hadir di rapat. → ×',
      'The department head can attend the meeting. → ×'),
]

kaiwa_turns = [
    (F, 'すみません、プリンターが 動かないんです。'),
    (Mx, '電源は 入って いますか。'),
    (F, 'はい、入って いますが、何も 出て きません。'),
    (Mx, 'マニュアルを 見ましたか。'),
    (F, 'いいえ、まだ 見て いません。'),
    (Mx, 'マニュアルを 見れば、わかる はずですよ。'),
]
kaiwa_qs = [
    {'id': f'{L}-kq1', 'prompt': ja('プリンターは どう なって いますか。'),
     'choices': [ja(c) for c in ['{電源|でんげん}が {入|はい}らない', '{電源|でんげん}は {入|はい}って いるが {動|うご}かない', '{紙|かみ}が ない']], 'answer': 1, 'audio': None,
     'translate': {'id': 'Bagaimana keadaan printer? — Menyala tetapi tidak bekerja.', 'en': 'What is wrong with the printer? — It is on but does not work.'}},
    {'id': f'{L}-kq2', 'prompt': ja('男《おとこ》の 人《ひと》は 何《なに》を すすめましたか。'),
     'choices': [ja(c) for c in ['{新|あたら}しい プリンターを {買|か}う', '{修理|しゅうり}に {出|だ}す', 'マニュアルを {見|み}る']], 'answer': 2, 'audio': None,
     'translate': {'id': 'Apa yang disarankan laki-laki itu? — Melihat buku panduan.', 'en': 'What did the man suggest? — Look at the manual.'}},
]

chokai = {
    'id': 'n3-10', 'level': 'N3',
    'focus': 'N3 Pelajaran 10 — …はずだ, …はずが／は ない, …はずだった, …ことが／も ある, Vた結果・Nの結果, V出す, V始める・終わる・続ける, V忘れる・合う・換える, …と いう ことに なる',
    'kaiwa': {'id': 'kaiwa',
              'audio_text': [{'speaker': s, 'text': t} for s, t in kaiwa_turns],
              'audio': '/audio/chokai/lesson-n3-10/kaiwa.mp3',
              'questions': kaiwa_qs},
    'questions': questions,
}
dump('public/data/chokai/lesson-n3-10.json', chokai)


def turn(sid, n, who, text, idt, ent):
    d = {'id': f'{sid}-t{n}', 'who': who, 'ja': ja(text), 'speak': speak(text),
         'id_text': idt, 'en_text': ent, 'audio': f'/audio/kaiwa/lesson-n3-10/{sid}-t{n}.mp3'}
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
    scenario(1, 'Yamada belum datang', 'Yamada has not arrived',
             'Di depan stasiun. Kamu dan rekan menunggu Yamada.',
             'In front of the station. You and a colleague are waiting for Yamada.',
             'Menyatakan perkiraan berdasar alasan dengan …はずだ.', 'State a reasoned expectation with ...hazu da.',
             ['…はずだ'], '中村', 'female', [
        (P, '{山田|やまだ}さん、まだ {来|き}ませんね。', 'Yamada belum datang ya.', 'Yamada has not come yet.'),
        (Y, '{十時|じゅうじ}の {電車|でんしゃ}に {乗|の}ると {言|い}って いたから、もうすぐ {着|つ}く はずですよ。', 'Katanya naik kereta jam sepuluh, jadi seharusnya sebentar lagi sampai.', 'He said he would take the ten o\'clock train, so he should arrive soon.'),
        (P, '{電話|でんわ}して みましょうか。', 'Mau coba telepon?', 'Shall we try calling?'),
        (Y, '{移動中|いどうちゅう}の はずですから、{少|すこ}し {待|ま}ちましょう。', 'Seharusnya sedang di perjalanan, jadi tunggu sebentar.', 'He should be on the way, so let us wait a bit.'),
        (P, 'そうですね。', 'Benar juga.', 'You are right.'),
        (Y, '{十分|じゅっぷん}ぐらいで {来|く}る はずです。', 'Seharusnya datang sekitar sepuluh menit.', 'He should be here in about ten minutes.')]),
    scenario(2, 'Dompet siapa ini?', 'Whose wallet is this?',
             'Di kantor. Teman menemukan dompet di lantai.',
             'At the office. A friend finds a wallet on the floor.',
             'Menyatakan hal yang mustahil dengan …はずが ない.', 'State impossibility with ...hazu ga nai.',
             ['…はずが／は ない'], '佐藤', 'male', [
        (P, 'この {財布|さいふ}、{鈴木|すずき}さんのかな。', 'Dompet ini punya Suzuki, ya?', 'Is this wallet Suzuki\'s?'),
        (Y, '{鈴木|すずき}さんは {先週|せんしゅう}から {出張中|しゅっちょうちゅう}だから、{会社|かいしゃ}に ある はずが ないよ。', 'Suzuki dinas luar sejak minggu lalu, mustahil ada di kantor.', 'Suzuki has been on a business trip since last week, so it cannot be here.'),
        (P, 'そうか。じゃあ、どうしよう。', 'Begitu. Lalu bagaimana?', 'I see. Then what shall we do?'),
        (Y, '{受付|うけつけ}に {届|とど}けよう。', 'Kita serahkan ke resepsionis.', 'Let us hand it in at reception.'),
        (P, '{持|も}ち{主|ぬし}は わかるかな。', 'Pemiliknya bisa ketahuan?', 'I wonder if they can find the owner.'),
        (Y, '{財布|さいふ}の {中|なか}に {名前|なまえ}が ある はずだよ。', 'Di dalam dompet seharusnya ada namanya.', 'There should be a name inside the wallet.')]),
    scenario(3, 'Pertandingan yang batal ditonton', 'The match I could not watch',
             'Pagi di kantor. Rekan menanyakan pertandingan kemarin.',
             'Morning at the office. A colleague asks about yesterday\'s match.',
             'Menyatakan rencana yang gagal dengan …はずだった.', 'State a plan that fell through with ...hazu datta.',
             ['…はずだった'], '山田', 'female', [
        (P, '{昨日|きのう}の {試合|しあい}、{見|み}に {行|い}きましたか。', 'Pertandingan kemarin, apakah Anda menonton?', 'Did you go to see yesterday\'s match?'),
        (Y, '{行|い}く はずだったんですが、{急|きゅう}に {残業|ざんぎょう}に なって しまって。', 'Seharusnya saya pergi, tapi tiba-tiba lembur.', 'I was supposed to go, but I suddenly had to work overtime.'),
        (P, 'それは {残念|ざんねん}でしたね。', 'Sayang sekali ya.', 'That is too bad.'),
        (Y, 'ええ。チケットも {買|か}って あったのに。', 'Ya. Padahal tiket sudah dibeli.', 'Yes. And I had even bought the ticket.'),
        (P, '{来週|らいしゅう}も {試合|しあい}が ありますよ。', 'Minggu depan juga ada pertandingan.', 'There is another match next week.'),
        (Y, '{来週|らいしゅう}は {行|い}ける はずです。', 'Minggu depan seharusnya saya bisa pergi.', 'I should be able to go next week.')]),
    scenario(4, 'Mesin baru dan hasil pengukuran', 'The new machine and the measurement',
             'Di pabrik. Atasan menanyakan mesin baru dan survei kebisingan.',
             'At the factory. A boss asks about the new machine and the noise survey.',
             'Menyatakan hal yang kadang terjadi dengan ことが ある dan hasil dengan 結果.', 'State occasional events with koto ga aru and outcomes with kekka.',
             ['…ことが／も ある', 'Vた結果'], '木村', 'male', [
        (P, '{新|あたら}しい {機械|きかい}は どうですか。', 'Bagaimana mesin baru?', 'How is the new machine?'),
        (Y, '{便利|べんり}ですが、ときどき {止|と}まる ことが あるんです。', 'Praktis, tapi kadang-kadang berhenti.', 'It is convenient, but it sometimes stops.'),
        (P, '{原因|げんいん}は わかりましたか。', 'Penyebabnya sudah diketahui?', 'Do you know the cause?'),
        (Y, 'いいえ。{動|うご}かない ことも ありますから、メーカーに {連絡|れんらく}しようと {思|おも}って います。', 'Belum. Kadang tidak jalan, jadi saya mau menghubungi pabrikan.', 'No. It sometimes does not run, so I plan to contact the manufacturer.'),
        (P, '{騒音|そうおん}の {調査|ちょうさ}は どう なりましたか。', 'Bagaimana survei kebisingan?', 'How did the noise survey go?'),
        (Y, '{測定|そくてい}した {結果|けっか}、{基準|きじゅん}を {超|こ}えて いる {場所|ばしょ}が ありました。', 'Hasil pengukuran, ada tempat yang melebihi standar.', 'As a result of measuring, some places exceeded the standard.')]),
    scenario(5, 'Hujan mulai turun dan laporan', 'Rain starts and the report',
             'Di kafe. Hujan tiba-tiba turun, lalu temanmu menanyakan laporanmu.',
             'At a cafe. Rain suddenly starts, then a friend asks about your report.',
             'Memakai V出す／始める／終わる／続ける.', 'Use V-dasu / hajimeru / owaru / tsuzukeru.',
             ['V出す', 'V始める・終わる・続ける'], '高橋', 'female', [
        (P, '{外|そと}、{見|み}て。{雨|あめ}が {降|ふ}り{出|だ}したよ。', 'Lihat luar. Hujan mulai turun.', 'Look outside. It has started raining.'),
        (Y, '{本当|ほんとう}だ。{止|や}むまで ここで {待|ま}ちましょう。', 'Benar. Kita tunggu di sini sampai reda.', 'Right. Let us wait here until it stops.'),
        (P, 'レポートは もう {書|か}き{終|お}わりましたか。', 'Laporannya sudah selesai ditulis?', 'Have you finished writing the report?'),
        (Y, 'いいえ、まだ {書|か}き{始|はじ}めた ところです。', 'Belum, baru mulai menulis.', 'No, I have only just started writing.'),
        (P, '{明日|あした}までですよ。', 'Tenggatnya besok lho.', 'It is due tomorrow.'),
        (Y, 'わかって います。{今夜|こんや}は {夜中|よなか}まで {書|か}き{続|つづ}けます。', 'Saya tahu. Malam ini saya terus menulis sampai tengah malam.', 'I know. Tonight I will keep writing until midnight.')]),
    scenario(6, 'Lupa membeli tiket', 'Forgot to buy the ticket',
             'Di stasiun. Temanmu lupa membeli tiket dan menanyakan ganti kereta.',
             'At the station. A friend forgot to buy a ticket and asks about the transfer.',
             'Memakai V忘れる／合う／換える dan …ということに なる.', 'Use V-wasureru / au / kaeru and ...to iu koto ni naru.',
             ['V忘れる・合う・換える', '…と いう ことに なる'], '鈴木', 'male', [
        (P, 'あっ、{切符|きっぷ}を {買|か}い{忘|わす}れちゃった。', 'Aduh, lupa membeli tiket.', 'Oh no, I forgot to buy a ticket.'),
        (Y, '{今|いま}なら まだ {間|ま}に {合|あ}うよ。{窓口|まどぐち}で {買|か}って おいで。', 'Sekarang masih sempat. Beli di loket.', 'You can still make it. Go buy one at the window.'),
        (P, 'うん。ところで、{乗|の}り{換|か}えは どこだっけ？', 'Iya. Omong-omong, ganti kereta di mana ya?', 'Okay. By the way, where do we transfer?'),
        (Y, '{次|つぎ}の {駅|えき}で {乗|の}り{換|か}えるんだよ。{二人|ふたり}で {確|たし}かめ{合|あ}おう。', 'Di stasiun berikutnya. Mari kita periksa bersama.', 'At the next station. Let us double-check together.'),
        (P, '{部長|ぶちょう}は {金曜日|きんようび}に {戻|もど}るけど、{会議|かいぎ}は {木曜日|もくようび}だよね。', 'Kepala bagian kembali hari Jumat, tapi rapat hari Kamis, ya.', 'The department head comes back Friday, but the meeting is Thursday, right?'),
        (Y, 'ということは、{部長|ぶちょう}は {会議|かいぎ}に {出|で}られないと いう ことに なるね。', 'Artinya, kepala bagian tidak bisa hadir di rapat.', 'That means the department head cannot attend the meeting.')]),
]
kaiwa = {'id': 10, 'level': 'N3',
         'title_id': 'Pelajaran 10 (N3): Dugaan, Kesalahan, dan Kejadian',
         'title_en': 'N3 Lesson 10: Expectations, Mistakes and Events',
         'scenarios': scenarios}
dump('public/data/kaiwa/lesson-n3-10.json', kaiwa)
dump('public/data/kaiwa/index-n3.json', {'lessons': [
    {'id': n, 'file': f'lesson-n3-{n}.json', 'track': 'lesson', 'level': 'N3'} for n in range(1, 11)]})
print('ok', len(questions), len(scenarios))
