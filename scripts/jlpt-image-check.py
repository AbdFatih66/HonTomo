#!/usr/bin/env python3
"""Cek gambar soal JLPT yang belum ada di public/.

Pakai:  python3 scripts/jlpt-image-check.py [root] [folder-bank]
Contoh: python3 scripts/jlpt-image-check.py . resources/lang-data/jlpt/n5

Membaca semua *.json di folder bank (default: soal asli, jlpt/n5), mengumpulkan
setiap { "image": "/images/...", "alt": ... } (gambar soal & gambar pilihan),
lalu melaporkan berkas PNG yang belum ada. Keluar dengan kode 1 bila ada yang hilang.
"""
import json, glob, os, sys


def walk(node, found):
    if isinstance(node, dict):
        if isinstance(node.get('image'), str) and node['image'].startswith('/images/'):
            found.add(node['image'])
        for v in node.values():
            walk(v, found)
    elif isinstance(node, list):
        for v in node:
            walk(v, found)


def main():
    root = sys.argv[1] if len(sys.argv) > 1 else '.'
    bank = sys.argv[2] if len(sys.argv) > 2 else 'resources/lang-data/jlpt/n5'
    found = set()
    for f in glob.glob(os.path.join(root, bank, '*.json')):
        walk(json.load(open(f, encoding='utf-8')), found)
    missing = sorted(p for p in found if not os.path.exists(os.path.join(root, 'public', p.lstrip('/'))))
    for p in missing:
        print('HILANG', p)
    print(f'{len(found) - len(missing)}/{len(found)} gambar ada, {len(missing)} belum dibuat')
    return 1 if missing else 0


if __name__ == '__main__':
    sys.exit(main())
