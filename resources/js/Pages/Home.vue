<script setup>
import Booking from '@/Components/Home/Booking.vue';
import Feature from '@/Components/Home/Feature.vue';
import Footer from '@/Components/Home/Footer.vue';
import Hero from '@/Components/Home/Hero.vue';
import Navbar from '@/Components/Home/Navbar.vue';
import Services from '@/Components/Home/Services.vue';
import Team from '@/Components/Home/Team.vue';
import Testimonials from '@/Components/Home/Testimonials.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, onMounted, defineProps, watch } from 'vue';

const props = defineProps({
    employees: Array,
    services: Array,
    minDate: String,
    maxDate: String,
    testimonials: Array,
});

const page = usePage();

onMounted(() => {
  document.querySelectorAll('a[href^="#"]').forEach(a=>{
    a.addEventListener('click',(e)=>{
      const target = document.querySelector(a.getAttribute('href'))
      if(!target) return
      e.preventDefault()
      const header = document.querySelector('header')
      const off = header ? header.offsetHeight : 0
      const y = target.getBoundingClientRect().top + window.pageYOffset - off - 8
      window.scrollTo({ top: y, behavior:'smooth' })
    })
  })
})


watch(page.props.flash, (flash) => {
    if (flash.success) {
        useToast().success(flash.success);
    }else if (flash.error) {
        useToast().error(flash.error);
    }
});
</script>

<template>
    <Head title="Home" />



    <!-- Navbar -->
    <Navbar />

  <!-- Hero -->
  <Hero />

  <!-- Features -->
  <Feature />

  <!-- Services & Prices -->
  <Services :services="services" />

  <!-- Team -->
  <Team :employees="employees" />

  <!-- Testimonials -->
  <Testimonials :testimonials="testimonials"/>

  <!-- Booking -->
  <Booking :employees="employees" :services="services" :minDate="minDate" :maxDate="maxDate" />

  <!-- Footer -->
    <Footer />

</template>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');
    body { background: #0d1117; }
    .service-card:hover { transform: translateY(-4px); transition: all 0.4s ease; }
    .glass-effect { backdrop-filter: blur(20px); background: rgba(17, 24, 39, 0.95); }
    .gradient-border { background: linear-gradient(135deg, #22c55e, #16a34a); padding: 1px; border-radius: 1rem; }
    .gradient-border-content { background: #111827; border-radius: calc(1rem - 1px); }
  </style>
