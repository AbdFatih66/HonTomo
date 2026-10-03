#!/usr/bin/env python3
"""Ubah bank paket orisinal (format `mock`) menjadi format `classic`.

Tujuan: paket HonTomo tampil di ujian dengan tata letak yang SAMA dengan soal asli
(satu halaman panjang + navigator untuk Mojigoi & Bunpou-Dokkai, pemutar rekaman
berurutan untuk Chokai), bukan satu もんだい per layar.

Pakai (dari root project):
    python3 scripts/jlpt-mock-to-classic.py resources/lang-data/jlpt/packs/n5-test-1

Berkas diubah di tempat. Aman dijalankan ulang: berkas yang sudah `classic`
dilewati. ID soal TIDAK berubah (mis. "v1-1", "g3-22", "l1-1"), jadi jawaban
attempt lama tetap cocok. `no` = nomor tampil di lembar soal.
"""
import json
import re
import sys
from pathlib import Path


def mark(s):
    """__kata__ -> ⟦kata⟧ (garis bawah) dan ［22］ -> {{22}} (kotak bernomor)."""
    if not isinstance(s, str):
        return s
    s = re.sub(r'__(.+?)__', r'⟦\1⟧', s)
    return re.sub(r'［(\d+)］', r'{{\1}}', s)


def mondai_number(m_idx):
    return str(m_idx + 1)


def convert_flyer(p, label):
    return {
        'kind': 'flyer',
        'label': label,
        'data': {
            'badge': p.get('badge', ''),
            'title': p.get('shop', ''),
            'hours': p.get('hours', ''),
            'tel': p.get('tel', ''),
            'sales': p.get('specials', []),
            'weekly_badge': p.get('weekly_title', ''),
            'weekly': p.get('weekly', []),
        },
    }


def convert_passage(p, label, boxed):
    kind = p.get('kind')
    if kind == 'flyer':
        return convert_flyer(p, label)
    if kind == 'memo':
        out = {
            'kind': 'note',
            'label': label,
            'to': mark(p.get('to', '')),
            'lines': [mark(x) for x in p.get('text', '').split('\n') if x.strip()],
            'from': mark(p.get('from', '')),
        }
        if p.get('lead'):
            out['lead'] = mark(p['lead'])
        if p.get('variant'):  # 'mail' = email (N4)
            out['variant'] = p['variant']
        return out
    if kind == 'notice':  # papan pengumuman: judul + butir ◆ (+ sub-butir ・) (N4)
        out = {
            'kind': 'notice',
            'label': label,
            'title': mark(p.get('title', '')),
            'items': [
                {k: ([mark(x) for x in v] if k == 'sub' else mark(v)) for k, v in it.items()}
                for it in p.get('items', [])
            ],
        }
        if p.get('lead'):
            out['lead'] = mark(p['lead'])
        return out
    if kind == 'poster':  # pengumuman bertabel (N4 もんだい 6); `data` dipakai apa adanya
        return {'kind': 'poster', 'label': label, 'data': p.get('data', {})}
    out = {'kind': 'text', 'boxed': p.get('boxed', boxed), 'label': label, 'body': mark(p.get('text', ''))}
    if 'label' in p:  # label eksplisit => `title` (tengah) & `author` (rata kanan) ikut tampil di bacaan (N4)
        for k in ('title', 'author'):
            if p.get(k):
                out[k] = mark(p[k])
    return out


def plain_script(turns):
    """Naskah rekaman untuk halaman pembahasan (hanya server / review)."""
    names = {'narrator': '', 'male': 'M：', 'female': 'F：'}
    lines = []
    for t in turns or []:
        lines.append(names.get(t.get('speaker'), '') + re.sub(r'《[^》]*》', '', t.get('text', '')))
    return '\n'.join(lines)


def convert_reading(bank):
    out = {
        'level': bank.get('level', 'N5'),
        'section': bank['section'],
        'title': bank.get('jp') or bank['section'],
        'minutes': bank['minutes'],
        'mondai': {},
        'questions': [],
    }
    passages = {}

    for mi, m in enumerate(bank['mondai']):
        no = mondai_number(mi)
        out['mondai'][no] = {'instruction': mark(m['instruction'])}
        if m.get('lead'):  # kalimat pengantar sebelum bacaan (N4 もんだい 3)
            out['mondai'][no]['lead'] = mark(m['lead'])
        if m.get('example'):  # れい (N4): tampil di bawah petunjuk, kunci ikut ditampilkan
            e = m['example']
            if e.get('type') == 'star':
                ex_stem = f"{e.get('prefix', '')}⟦　⟧ ⟦　⟧ ⟦★⟧ ⟦　⟧{e.get('suffix', '')}"
            else:
                ex_stem = mark(e.get('stem', ''))
            out['mondai'][no]['example'] = {
                'stem': ex_stem,
                'choices': [mark(c) for c in e['choices']],
                'answer': e['answer'],
            }
            if e.get('label'):
                out['mondai'][no]['example']['label'] = e['label']
            if e.get('howto'):  # cara menjawab ★ (petunjuk bergambar di lembar soal)
                out['mondai'][no]['example']['howto'] = e['howto']
        n_passages = sum(1 for g in m['groups'] if g.get('passage'))

        for gi, g in enumerate(m['groups']):
            pkey = None
            p = g.get('passage')
            if p:
                pkey = f"{m['id']}-{gi + 1}"
                if 'label' in p:  # label eksplisit (boleh kosong) mengalahkan aturan di bawah
                    label = p['label']
                elif p.get('title') and p.get('kind') in (None, 'text', 'memo'):
                    label = f"({gi + 1}) {p['title']}"
                elif n_passages > 1:
                    label = f'({gi + 1})'
                else:
                    label = ''
                passages[pkey] = convert_passage(p, label, boxed=(m['id'] == 'g3'))

            for q in g['questions']:
                if q.get('type') == 'star':
                    stem = f"{q.get('prefix', '')}⟦　⟧ ⟦　⟧ ⟦★⟧ ⟦　⟧{q.get('suffix', '')}"
                else:
                    stem = mark(q.get('stem', ''))
                nq = {
                    'id': q['id'],
                    'no': q['no'],
                    'mondai': mi + 1,
                    'stem': stem,
                    'choices': [mark(c) for c in q['choices']],
                    'answer': q['answer'],
                }
                if q.get('exp'):
                    nq['exp'] = q['exp']
                if pkey:
                    nq['passage'] = pkey
                out['questions'].append(nq)

    if passages:
        out['passages'] = passages
    return out


def convert_listening(bank):
    out = {
        'level': bank.get('level', 'N5'),
        'section': 'chokai',
        'title': bank.get('jp') or '聴解',
        'minutes': bank['minutes'],
        'mondai': {},
        'questions': [],
    }

    # penjelasan umum & penutup rekaman (opsional; N4)
    audio = {k: bank[k]['audio'] for k in ('intro', 'outro') if bank.get(k, {}).get('audio')}
    if audio:
        out['audio'] = audio

    def item_fields(q, with_key):
        f = {}
        if q.get('audio'):
            f['audio'] = q['audio']
        f['answer'] = q['answer']
        choices = q.get('choices') or []
        if q.get('hidden_choices'):
            f['choice_count'] = len(choices)  # hanya terdengar, seperti ujian asli
        else:
            f['choices'] = [mark(c) if isinstance(c, str) else c for c in choices]
        if q.get('image'):
            f['image'] = q['image']
        if q.get('arrow'):
            f['arrow'] = q['arrow']
        if q.get('marks'):
            f['marks'] = q['marks']  # label ア・イ・ウ・エ di atas satu gambar (N4)
        script = plain_script(q.get('transcript') or q.get('audio_text'))
        if script:
            f['script'] = script
        if with_key and q.get('exp'):
            f['exp'] = q['exp']
        return f

    for mi, m in enumerate(bank['mondai']):
        no = mondai_number(mi)
        entry = {
            'instruction': m['instruction'],
            'answer_seconds': m.get('gap', 8),
        }
        if m.get('before', {}).get('audio'):
            entry['audio_before'] = m['before']['audio']  # trek istirahat sebelum もんだい (N4: sebelum 3)
        if m.get('intro', {}).get('audio'):
            entry['audio'] = m['intro']['audio']
        if m.get('memo'):
            entry['memo'] = True  # kolom memo (もんだい 4)
        if m.get('example'):
            entry['example'] = item_fields(m['example'], with_key=False)
        out['mondai'][no] = entry

        for g in m['groups']:
            for q in g['questions']:
                nq = {'id': q['id'], 'no': q['no'], 'mondai': mi + 1}
                nq.update(item_fields(q, with_key=True))
                out['questions'].append(nq)

    return out


def main(folder):
    folder = Path(folder)
    for name in ('mojigoi', 'bunpou_dokkai', 'chokai'):
        path = folder / f'{name}.json'
        if not path.exists():
            print(f'lewati {path} (tidak ada; pack ini belum punya sesi tsb)')
            continue
        bank = json.loads(path.read_text(encoding='utf-8'))
        if bank.get('format') != 'mock':
            print(f'lewati {path} (sudah classic)')
            continue
        new = convert_listening(bank) if name == 'chokai' else convert_reading(bank)
        keys = [q['id'] for q in new['questions']]
        assert len(keys) == len(set(keys)), f'id ganda di {name}'
        assert all(1 <= q['answer'] <= 4 for q in new['questions']), f'answer di luar 1..4 di {name}'
        path.write_text(json.dumps(new, ensure_ascii=False, indent=2) + '\n', encoding='utf-8')
        print(f"{path}: {len(new['questions'])} soal")


if __name__ == '__main__':
    if len(sys.argv) != 2:
        sys.exit(__doc__)
    main(sys.argv[1])
