<script setup>
// Required, not decorative: KanjiVG, KANJIDIC2, and JMdict are all
// CC BY-SA licensed, which requires attribution wherever the data is
// used (see docs/kanji-module.md section 7). Linked from the Kanji
// chart page footer.
const { t } = useI18n()

const sources = [
  {
    name: 'KanjiVG',
    author: 'Ulrich Apel',
    license: 'CC BY-SA 3.0',
    licenseUrl: 'https://creativecommons.org/licenses/by-sa/3.0/',
    url: 'https://kanjivg.tagaini.net',
    usedForKey: 'kanji.sources_kanjivg_used_for',
  },
  {
    name: 'KANJIDIC2',
    author: 'Electronic Dictionary Research and Development Group (Jim Breen et al.)',
    license: 'CC BY-SA 4.0',
    licenseUrl: 'https://creativecommons.org/licenses/by-sa/4.0/',
    url: 'https://www.edrdg.org/wiki/index.php/KANJIDIC_Project',
    usedForKey: 'kanji.sources_kanjidic_used_for',
  },
  {
    name: 'JMdict',
    author: 'Electronic Dictionary Research and Development Group (Jim Breen et al.)',
    license: 'CC BY-SA 4.0',
    licenseUrl: 'https://creativecommons.org/licenses/by-sa/4.0/',
    url: 'https://www.edrdg.org/jmdict/j_jmdict.html',
    usedForKey: 'kanji.sources_jmdict_used_for',
  },
]
</script>

<template>
  <div>
    <h4 class="text-h4 mb-2">
      {{ t('kanji.sources_title') }}
    </h4>
    <p class="text-body-1 text-medium-emphasis mb-6">
      {{ t('kanji.sources_intro') }}
    </p>

    <VRow>
      <VCol
        v-for="source in sources"
        :key="source.name"
        cols="12"
        md="4"
      >
        <VCard class="pa-4 h-100">
          <div class="text-h6 mb-1">
            {{ source.name }}
          </div>
          <div class="text-caption text-medium-emphasis mb-3">
            {{ t('kanji.sources_by', { author: source.author }) }}
          </div>
          <p class="text-body-2 mb-4">
            {{ t(source.usedForKey) }}
          </p>
          <VChip
            size="small"
            variant="tonal"
            class="mb-4"
            :href="source.licenseUrl"
            tag="a"
            target="_blank"
            rel="noopener"
          >
            {{ source.license }}
          </VChip>
          <div>
            <VBtn
              :href="source.url"
              target="_blank"
              rel="noopener"
              variant="text"
              size="small"
              append-icon="tabler-external-link"
            >
              {{ t('kanji.sources_visit') }}
            </VBtn>
          </div>
        </VCard>
      </VCol>
    </VRow>

    <VAlert
      type="info"
      variant="tonal"
      class="mt-6"
    >
      {{ t('kanji.sources_disclaimer') }}
    </VAlert>
  </div>
</template>
