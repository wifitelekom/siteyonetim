<script setup lang="ts">
import { useTheme } from 'vuetify'

interface Props {
  notDue: number
  dueToday: number
  overdue: number
  total: number
}

const props = defineProps<Props>()

const vuetifyTheme = useTheme()

const chartOptions = computed(() => {
  const currentTheme = vuetifyTheme.current.value.colors

  return {
    chart: {
      type: 'donut' as const,
      parentHeightOffset: 0,
      toolbar: { show: false },
    },
    labels: ['Vadesi Gelmemis', 'Bugun Vadeli', 'Gecikimis'],
    colors: [currentTheme.info, currentTheme.warning, currentTheme.error],
    stroke: { width: 0 },
    dataLabels: {
      enabled: true,
      formatter: (val: number) => `${Math.round(val)}%`,
    },
    legend: {
      position: 'bottom' as const,
      labels: {
        colors: currentTheme['on-surface'],
      },
    },
    plotOptions: {
      pie: {
        donut: {
          size: '70%',
          labels: {
            show: true,
            name: { fontSize: '14px' },
            value: {
              fontSize: '18px',
              fontWeight: 600,
              formatter: (val: string) => {
                const num = Number.parseFloat(val)

                return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(num)
              },
            },
            total: {
              show: true,
              label: 'Toplam',
              fontSize: '14px',
              formatter: () => {
                return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(props.total)
              },
            },
          },
        },
      },
    },
    responsive: [{
      breakpoint: 576,
      options: {
        chart: { height: 280 },
        legend: { position: 'bottom' },
      },
    }],
  }
})

const series = computed(() => [props.notDue, props.dueToday, props.overdue])
</script>

<template>
  <VCard>
    <VCardItem>
      <VCardTitle>Alacak Durumu</VCardTitle>
      <VCardSubtitle>Vade bazli alacak dagilimi</VCardSubtitle>
    </VCardItem>

    <VCardText>
      <VueApexCharts
        type="donut"
        height="320"
        :options="chartOptions"
        :series="series"
      />
    </VCardText>
  </VCard>
</template>
