#!/usr/bin/env python3
"""Pembuat berkas N3 Pelajaran 4 (chokai + kaiwa) dari penanda {漢字|かな}.
Jalankan dari root project:  python3 scripts/gen_n3_l4.py
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
L = 'n3l4'
AUD = '/audio/chokai/lesson-n3-4'
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
    q(1, [(Mx, '山田さんから 電話が ありました。'),
          (F, '何と 言っていましたか。'),
          (Mx, '電車が 遅れていて、少し 遅れるということです。')],
      '山田《やまだ》さんは どう しますか。',
      ['{早|はや}く {来|き}ます', '{少|すこ}し {遅|おく}れて {来|き}ます', '{休|やす}みます'], 1,
      'Apa yang akan dilakukan Yamada? — Datang sedikit terlambat.',
      'What will Yamada do? — Come a little late.'),
    q(2, [(Mx, 'どこへ 行くの？'),
          (F, 'ちょっと 図書館へ。'),
          (Mx, '何を 借りるの？'),
          (F, '日本語の 本。明日 試験なの。')],
      '女《おんな》の 人《ひと》は どうして 図書館《としょかん》へ 行《い》きますか。',
      ['{友達|ともだち}に {会|あ}うからです', '{本|ほん}を {返|かえ}すからです', '{試験|しけん}が あるからです'], 2,
      'Mengapa perempuan itu pergi ke perpustakaan? — Karena besok ada ujian.',
      'Why is the woman going to the library? — She has an exam tomorrow.'),
    q(3, [(F, 'あ、また 財布を 忘れちゃった。'),
          (Mx, '取りに 行く？'),
          (F, 'ううん、友達が 待ってるから、このまま 行っちゃう。'),
          (Mx, 'じゃあ、お金を 貸しとくよ。')],
      '男《おとこ》の 人《ひと》は 何《なに》を しますか。',
      ['{財布|さいふ}を {取|と}りに {行|い}きます', 'お{金|かね}を {貸|か}します', '{友達|ともだち}を {待|ま}ちます'], 1,
      'Apa yang dilakukan laki-laki itu? — Meminjamkan uang.',
      'What does the man do? — He lends her money.'),
    q(4, [(F, '昨日の 飲み会、どうだった？'),
          (Mx, '疲れたよ。上司に 歌を 歌わされたんだ。'),
          (F, 'それは 大変だったね。'),
          (Mx, 'その うえ、最後まで 付き合わされたよ。')],
      '男《おとこ》の 人《ひと》は 昨日《きのう》 上司《じょうし》に 何《なに》を させられましたか。',
      ['{歌|うた}を {歌|うた}う こと', '{料理|りょうり}を {作|つく}る こと', '{運転|うんてん}する こと'], 0,
      'Apa yang dipaksakan atasan kepada laki-laki itu? — Menyanyi.',
      'What did the boss make the man do? — Sing.'),
    q(5, [(F, '息子さんは 最近 どうですか。'),
          (Mx, '新しい ゲームを 欲しがっているんです。'),
          (F, '友達が 持っているからですか。'),
          (Mx, 'ええ。でも 私が 買わないので、悔しがっています。')],
      '息子《むすこ》さんは どうして 悔《くや》しがって いますか。',
      ['{友達|ともだち}が いないからです', '{試験|しけん}に {落|お}ちたからです', '{親|おや}が ゲームを {買|か}わないからです'], 2,
      'Mengapa putranya kesal? — Karena orang tua tidak membelikan game.',
      'Why is the son frustrated? — His parent will not buy him the game.'),
    q(6, [(Mx, 'どこへ 行くの？')],
      '答《こた》えは どれですか。',
      ['はい、{行|い}きます。', 'きのう {行|い}きました。', 'ちょっと {図書館|としょかん}へ。'], 2,
      'Mau ke mana? — Sebentar ke perpustakaan.',
      'Where are you going? — Just to the library.'),
    q(7, [(Mx, '会議が 終わりましたら、お電話させて いただきます。')],
      '答《こた》えは どれですか。',
      ['ありがとうございます。お{待|ま}ちして おります。', 'いいえ、{終|お}わりました。', 'はい、{電話|でんわ}しました。'], 0,
      'Setelah rapat selesai, kami akan menelepon. — Terima kasih, kami tunggu.',
      'We will call you once the meeting ends. — Thank you, we will be waiting.'),
    q(8, [(Mx, '田中さんが 結婚した ことを 知って いますか。'),
          (F, 'ええ、聞きました。相手は 有名な 歌手だそうです。')],
      '女《おんな》の 人《ひと》は 田中《たなか》さんが 結婚《けっこん》した ことを 知《し》って います。', OX, 0,
      'Perempuan itu tahu Tanaka sudah menikah. → ○',
      'The woman knows Tanaka got married. → ○'),
    q(9, [(F, 'これは 論文ですから、「である」で 書いて ください。'),
          (Mx, 'わかりました。')],
      '論文《ろんぶん》では 「だ」を 使《つか》います。', OX, 1,
      'Dalam makalah dipakai "da". → ×',
      'In a thesis, "da" is used. → ×'),
    q(10, [(F, '朝は 起きて、顔を 洗い、お茶を 飲み、新聞を 読みます。'),
           (Mx, '忙しいですね。')],
      '女《おんな》の 人《ひと》は 朝《あさ》 コーヒーを 飲《の》みます。', OX, 1,
      'Perempuan itu minum kopi di pagi hari. → ×',
      'The woman drinks coffee in the morning. → ×'),
]

kaiwa_turns = [
    (F, 'お電話、ありがとうございます。ただいま 来客中です。'),
    (Mx, '恐れ入りますが、田中部長は いらっしゃいますか。'),
    (F, 'あいにく、ただいま 来客中です。'),
    (Mx, 'では、伝言を お願いします。'),
    (F, 'はい、お受けします。'),
    (Mx, '会議が 終わりましたら、お電話を いただけませんか。'),
]
kaiwa_qs = [
    {'id': f'{L}-kq1', 'prompt': ja('男《おとこ》の 人《ひと》は 何《なに》を 頼《たの》みましたか。'),
     'choices': [ja(c) for c in ['{注文|ちゅうもん}', '{予約|よやく}', '{伝言|でんごん}']], 'answer': 2, 'audio': None,
     'translate': {'id': 'Apa yang diminta laki-laki itu? — Menitip pesan.', 'en': 'What did the man ask for? — To leave a message.'}},
    {'id': f'{L}-kq2', 'prompt': ja('男《おとこ》の 人《ひと》は 部長《ぶちょう》に 何《なに》を して ほしいですか。'),
     'choices': [ja(c) for c in ['{会議|かいぎ}が {終|お}わったら {電話|でんわ}して ほしいです', '{会議|かいぎ}に {出|で}て ほしいです', '{会|あ}いに {来|き}て ほしいです']], 'answer': 0, 'audio': None,
     'translate': {'id': 'Apa yang diinginkan laki-laki itu dari kepala bagian? — Menelepon setelah rapat.', 'en': 'What does the man want the department head to do? — Call after the meeting.'}},
]

chokai = {
    'id': 'n3-4', 'level': 'N3',
    'focus': 'N3 Pelajaran 4 — …ということだ, …の／…の？, 〜ちゃう・〜とく・〜てる, 〜(さ)せられる, 〜である, 連用形, …こと・…ということ, N１の N２, Vましたら・Vまして',
    'kaiwa': {'id': 'kaiwa',
              'audio_text': [{'speaker': s, 'text': t} for s, t in kaiwa_turns],
              'audio': '/audio/chokai/lesson-n3-4/kaiwa.mp3',
              'questions': kaiwa_qs},
    'questions': questions,
}
dump('public/data/chokai/lesson-n3-4.json', chokai)


def turn(sid, n, who, text, idt, ent):
    d = {'id': f'{sid}-t{n}', 'who': who, 'ja': ja(text), 'speak': speak(text),
         'id_text': idt, 'en_text': ent, 'audio': f'/audio/kaiwa/lesson-n3-4/{sid}-t{n}.mp3'}
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
    scenario(1, 'Pesan telepon dari Yamada', 'A phone message from Yamada',
             'Pagi di kantor. Rekan kerja menyampaikan pesan telepon untukmu.',
             'Morning at the office. A colleague passes on a phone message to you.',
             'Mengulang pesan tidak langsung dengan …ということだ／ということです.', 'Repeat a reported message with ...to iu koto da / to iu koto desu.',
             ['…ということだ'], '中村', 'female', [
        (P, '{山田|やまだ}さんから {電話|でんわ}が ありましたよ。', 'Ada telepon dari Yamada.', 'There was a call from Yamada.'),
        (Y, '{何|なん}と {言|い}って いましたか。', 'Dia bilang apa?', 'What did he say?'),
        (P, '{電車|でんしゃ}が {遅|おく}れて いて、{少|すこ}し {遅|おく}れるということです。', 'Kereta terlambat, katanya dia akan datang agak telat.', 'The train is delayed, so he says he will be a little late.'),
        (Y, '{少|すこ}し {遅|おく}れるということですね。わかりました。', 'Jadi dia akan sedikit terlambat. Baik.', 'So he will be a little late. Understood.'),
        (P, '{会議|かいぎ}は {先|さき}に {始|はじ}めましょうか。', 'Mulai rapat duluan saja?', 'Shall we start the meeting first?'),
        (Y, 'はい、{先|さき}に {始|はじ}めましょう。', 'Ya, kita mulai duluan.', 'Yes, let us start first.')]),
    scenario(2, 'Ngobrol akrab', 'A casual chat',
             'Di lorong kampus. Teman dekat bertanya kamu mau ke mana.',
             'In a hallway. A close friend asks where you are going.',
             'Bicara santai dengan の？／なの／ちゃう／とく／てる.', 'Speak casually with no? / na no / chau / toku / teru.',
             ['…の？', '〜ちゃう', '〜とく', '〜てる'], '佐藤', 'male', [
        (P, 'どこへ {行|い}くの？', 'Mau ke mana?', 'Where are you going?'),
        (Y, 'ちょっと {図書館|としょかん}へ。', 'Sebentar ke perpustakaan.', 'Just to the library.'),
        (P, '{何|なに}を {借|か}りるの？', 'Mau pinjam apa?', 'What are you going to borrow?'),
        (Y, '{日本語|にほんご}の {本|ほん}。{明日|あした} {試験|しけん}なの。', 'Buku bahasa Jepang. Besok ujian.', 'A Japanese book. I have an exam tomorrow.'),
        (P, '{財布|さいふ}は {持|も}ってる？ なかったら お{金|かね}を {貸|か}しとくよ。', 'Dompetmu dibawa? Kalau tidak, kupinjami uang.', 'Do you have your wallet? If not, I will lend you some money.'),
        (Y, 'あ、また {忘|わす}れちゃった。', 'Ah, ketinggalan lagi.', 'Oh, I forgot it again.')]),
    scenario(3, 'Dipaksa atasan', 'Made to do something by the boss',
             'Pagi hari. Rekan kerja menanyakan pesta minum semalam.',
             'Morning. A colleague asks about last night\'s drinking party.',
             'Mengungkapkan hal yang terpaksa dengan 〜(さ)せられる.', 'Express forced actions with ~(sa)serareru.',
             ['〜(さ)せられる・〜される'], '山田', 'female', [
        (P, '{昨日|きのう}の {飲|の}み{会|かい}、どうでしたか。', 'Pesta minum kemarin bagaimana?', 'How was last night\'s drinking party?'),
        (Y, '{疲|つか}れました。{上司|じょうし}に {歌|うた}を {歌|うた}わされたんです。', 'Capek. Saya dipaksa atasan menyanyi.', 'I was tired. My boss made me sing.'),
        (P, 'それは {大変|たいへん}でしたね。', 'Wah, repot ya.', 'That must have been hard.'),
        (Y, 'その うえ、{最後|さいご}まで {付|つ}き{合|あ}わされました。', 'Apalagi, saya dipaksa menemani sampai akhir.', 'On top of that, I was made to stay until the very end.'),
        (P, 'お{疲|つか}れさまでした。', 'Terima kasih atas kerja kerasnya.', 'Thank you for your hard work.'),
        (Y, 'ありがとうございます。', 'Terima kasih.', 'Thank you.')]),
    scenario(4, 'Menulis laporan gaya tulis', 'Writing a report in formal style',
             'Di kantor. Kamu bertanya cara menulis kesimpulan laporan.',
             'At the office. You ask how to write the conclusion of a report.',
             'Memakai gaya である dan bentuk penghubung.', 'Use the de aru style and the connective form.',
             ['〜である', '連用形'], '木村', 'male', [
        (P, 'レポートは できましたか。', 'Laporannya sudah selesai?', 'Is the report finished?'),
        (Y, 'まだです。{結論|けつろん}は どう {書|か}けば いいでしょうか。', 'Belum. Kesimpulannya bagaimana menulisnya?', 'Not yet. How should I write the conclusion?'),
        (P, '{論文|ろんぶん}ですから、「〜だ」ではなく「〜である」で {書|か}いて ください。', 'Karena makalah, tulis dengan "de aru", bukan "da".', 'Since it is a paper, write with "de aru", not "da".'),
        (Y, 'たとえば、「この {問題|もんだい}は {重要|じゅうよう}である」と {書|か}くのですか。', 'Misalnya, menulis "masalah ini penting", begitu?', 'For example, I write "this problem is important"?'),
        (P, 'ええ、そうです。「この {町|まち}は {静|しず}かで、{住|す}みやすい」のように {文|ぶん}を つなげても いいですよ。', 'Ya. Boleh juga menyambung kalimat seperti "kota ini tenang dan nyaman".', 'Yes. You can also connect sentences like "this town is quiet and easy to live in".'),
        (Y, 'わかりました。やって みます。', 'Baik. Akan saya coba.', 'Understood. I will try.')]),
    scenario(5, 'Kabar pernikahan Tanaka', 'News of Tanaka\'s marriage',
             'Istirahat siang. Rekan kerja membahas kabar tentang Tanaka.',
             'Lunch break. A colleague discusses news about Tanaka.',
             'Membendakan kalimat dengan …こと／…ということ dan N１の N２.', 'Nominalize with ...koto / ...to iu koto and N1 no N2.',
             ['…こと・…ということ', 'N１の N２'], '高橋', 'female', [
        (P, '{田中|たなか}さんが {結婚|けっこん}した ことを {知|し}って いますか。', 'Apakah Anda tahu Tanaka sudah menikah?', 'Do you know that Tanaka got married?'),
        (Y, 'ええ、{聞|き}きました。', 'Ya, saya dengar.', 'Yes, I heard.'),
        (P, '{相手|あいて}が {有名|ゆうめい}な {歌手|かしゅ}だという ことも {知|し}って いますか。', 'Apakah Anda juga tahu pasangannya penyanyi terkenal?', 'Do you also know that the partner is a famous singer?'),
        (Y, 'えっ、それは {知|し}りませんでした。', 'Eh, itu saya tidak tahu.', 'What, I did not know that.'),
        (P, '{部長|ぶちょう}の {田中|たなか}さんも {知|し}らなかったそうです。', 'Katanya kepala bagian, Pak Tanaka, juga tidak tahu.', 'I hear even Tanaka, the department head, did not know.'),
        (Y, 'そうですか。{課長|かちょう}の {山田|やまだ}さんは {知|し}って いますか。', 'Begitu. Apakah Yamada, kepala seksi, tahu?', 'I see. Does Yamada, the section chief, know?')]),
    scenario(6, 'Telepon bisnis yang sopan', 'A polite business call',
             'Sore hari. Rekan bisnis berterima kasih atas kunjunganmu.',
             'Evening. A business partner thanks you for visiting.',
             'Berbicara sopan dengan Vましたら／Vまして.', 'Speak politely with V-mashitara / V-mashite.',
             ['Vましたら、〜', 'Vまして、〜'], '鈴木', 'male', [
        (P, '{本日|ほんじつ}は お{越|こ}し いただきまして、ありがとうございました。', 'Terima kasih telah berkenan hadir hari ini.', 'Thank you for coming today.'),
        (Y, 'こちらこそ、ありがとうございました。', 'Saya juga berterima kasih.', 'Thank you as well.'),
        (P, '{会議|かいぎ}が {終|お}わりましたら、こちらから お{電話|でんわ}させて いただきます。', 'Setelah rapat selesai, kami akan menelepon.', 'Once the meeting is over, we will call you.'),
        (Y, 'ありがとうございます。お{待|ま}ちして おります。', 'Terima kasih. Kami tunggu.', 'Thank you. We will be waiting.'),
        (P, '{何時|なんじ}ごろが よろしいでしょうか。', 'Sekitar jam berapa baiknya?', 'What time would suit you?'),
        (Y, '{四時|よじ}ごろですと、{助|たす}かります。', 'Kalau sekitar jam empat, sangat membantu.', 'Around four o\'clock would be a great help.')]),
]
kaiwa = {'id': 4, 'level': 'N3',
         'title_id': 'Pelajaran 4 (N3): Pesan, Bahasa Lisan, dan Gaya Tulis',
         'title_en': 'N3 Lesson 4: Messages, Spoken Style and Written Style',
         'scenarios': scenarios}
dump('public/data/kaiwa/lesson-n3-4.json', kaiwa)
dump('public/data/kaiwa/index-n3.json', {'lessons': [
    {'id': n, 'file': f'lesson-n3-{n}.json', 'track': 'lesson', 'level': 'N3'} for n in (1, 2, 3, 4)]})
print('ok', len(questions), len(scenarios))
