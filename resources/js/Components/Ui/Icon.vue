<script setup>
import { computed } from 'vue';

const props = defineProps({
    name: { type: String, required: true },
    size: { type: [Number, String], default: 20 },
    strokeWidth: { type: [Number, String], default: 1.75 },
    filled: { type: Boolean, default: false },
});

const paths = {
    menu: 'M4 6h16M4 12h16M4 18h16',
    close: 'M6 6l12 12M18 6L6 18',
    chevronDown: 'm6 9 6 6 6-6',
    chevronUp: 'm6 15 6-6 6 6',
    chevronLeft: 'm15 6-6 6 6 6',
    chevronRight: 'm9 6 6 6-6 6',
    arrowRight: 'M5 12h14m-6-6 6 6-6 6',
    arrowLeft: 'M19 12H5m6 6-6-6 6-6',
    arrowUpRight: 'M7 17 17 7m0 0H8m9 0v9',
    externalLink: 'M15 3h6v6M10 14 21 3M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6',

    sun: 'M12 3v2m0 14v2M5.6 5.6l1.4 1.4m10 10 1.4 1.4M3 12h2m14 0h2M5.6 18.4 7 17m10-10 1.4-1.4M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z',
    moon: 'M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8Z',
    globe: 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm0 0c2.2-2.3 3.4-5.6 3.4-9S14.2 5.3 12 3M12 21c-2.2-2.3-3.4-5.6-3.4-9S9.8 5.3 12 3M3.5 9h17M3.5 15h17',

    dashboard: 'M4 4h7v7H4zM13 4h7v4h-7zM13 10h7v10h-7zM4 13h7v7H4z',
    users: 'M16 20v-1.5A3.5 3.5 0 0 0 12.5 15h-5A3.5 3.5 0 0 0 4 18.5V20M10 11.5A3.75 3.75 0 1 0 10 4a3.75 3.75 0 0 0 0 7.5ZM20 20v-1.5a3.5 3.5 0 0 0-2.6-3.4M15.5 4.2a3.75 3.75 0 0 1 0 7',
    whistle: 'M9.5 8.5h9a2.5 2.5 0 0 1 0 5h-4l-2.2 4.4a2 2 0 0 1-1.8 1.1H8a5 5 0 0 1 0-10h1.5Zm-1 2.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5Z',
    ball: 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18ZM12 8.8l3.05 2.21-1.17 3.58h-3.76L8.95 11.01 12 8.8ZM12 8.8V3M15.05 11.01l5.45-1.81M13.88 14.59l3.42 4.71M10.12 14.59 6.7 19.3M8.95 11.01 3.5 9.2',
    newspaper: 'M4 5h13v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5Zm13 3h2a1 1 0 0 1 1 1v9a2 2 0 0 1-2 2M7 9h7M7 12h7M7 15h4',
    page: 'M6 3h7l5 5v13H6V3Zm7 0v5h5M9 13h6M9 17h6',
    settings: 'M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z M19.4 15a1.6 1.6 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.6 1.6 0 0 0-1.8-.3 1.6 1.6 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1A1.6 1.6 0 0 0 9 19.4a1.6 1.6 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.6 1.6 0 0 0 .3-1.8 1.6 1.6 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1A1.6 1.6 0 0 0 4.6 9a1.6 1.6 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.6 1.6 0 0 0 1.8.3H9a1.6 1.6 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.6 1.6 0 0 0 1 1.5 1.6 1.6 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.6 1.6 0 0 0-.3 1.8V9a1.6 1.6 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.6 1.6 0 0 0-1.5 1Z',
    mail: 'M4 6h16v12H4zM4 7l8 6 8-6',
    home: 'M4 10.5 12 4l8 6.5V20a1 1 0 0 1-1 1h-4v-6H9v6H5a1 1 0 0 1-1-1v-9.5Z',

    plus: 'M12 5v14M5 12h14',
    minus: 'M5 12h14',
    edit: 'M4 20h4L19 9a2.1 2.1 0 0 0-3-3L5 17v3ZM14.5 6.5l3 3',
    trash: 'M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3',
    save: 'M5 4h11l3 3v13H5V4Zm3 0v6h7V4M8 20v-6h8v6',
    search: 'M11 18a7 7 0 1 0 0-14 7 7 0 0 0 0 14Zm5-2 4 4',
    filter: 'M4 5h16l-6 7v6l-4 2v-8L4 5Z',
    upload: 'M12 16V4m0 0L8 8m4-4 4 4M4 17v2a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-2',
    image: 'M4 5h16v14H4zM8.5 11a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3ZM4 16l4.5-4.5L14 17M14 14l2.5-2.5L20 15',
    drag: 'M9 6h.01M9 12h.01M9 18h.01M15 6h.01M15 12h.01M15 18h.01',
    check: 'm5 13 4 4L19 7',
    star: 'm12 4 2.4 5 5.6.8-4 4 .9 5.6-4.9-2.6L7 19.4l1-5.6-4-4 5.6-.8L12 4Z',
    eye: 'M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12Zm9.5 2.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z',
    eyeOff: 'M4 4l16 16M10.6 10.6a2.5 2.5 0 0 0 3.4 3.4M6.7 6.9C4 8.6 2.5 12 2.5 12S6 18.5 12 18.5c1.6 0 3-.4 4.2-1M17.6 15.2c2.3-1.6 3.9-3.2 3.9-3.2S18 5.5 12 5.5c-.7 0-1.4.1-2 .3',
    logout: 'M15 17l5-5-5-5M20 12H9M12 20H6a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1h6',
    copy: 'M9 9h10v10a1 1 0 0 1-1 1H9zM15 9V5a1 1 0 0 0-1-1H5v10a1 1 0 0 0 1 1h3',

    calendar: 'M4 7h16v13H4zM4 11h16M8 4v4M16 4v4',
    clock: 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm0-14v5l3.5 2',
    location: 'M12 21s7-5.5 7-11a7 7 0 1 0-14 0c0 5.5 7 11 7 11Zm0-8.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z',
    phone: 'M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a1 1 0 0 1-1 1A15 15 0 0 1 4 5a1 1 0 0 1 1-1Z',
    flag: 'M6 21V4m0 0h12l-3 4.5L18 13H6',
    trophy: 'M8 4h8v5a4 4 0 0 1-8 0V4ZM8 6H5v1a3 3 0 0 0 3 3M16 6h3v1a3 3 0 0 1-3 3M10 17h4M9 20h6M12 13v4',
    ruler: 'M4 14 14 4l6 6L10 20l-6-6Zm3-3 2 2m1-4 2 2m1-4 2 2',
    scale: 'M12 4v3m-7 3h14l-2 9H7L5 10Zm7-3a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Z',
    target: 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm0-4.5a4.5 4.5 0 1 0 0-9 4.5 4.5 0 0 0 0 9Zm0-3a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Z',
    quote: 'M9 7H5v5h4v-2c0 2.8-1.4 4.5-3 5m13-8h-4v5h4v-2c0 2.8-1.4 4.5-3 5',
    chart: 'M4 20V4M4 20h16M8 16V10M12 16V6M16 16v-4',
    info: 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm0-9v5m0-8h.01',
    warning: 'M12 4 2.5 20h19L12 4Zm0 6v4m0 3h.01',
    alert: 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm0-13v5m0 3h.01',

    instagram: 'M7 3h10a4 4 0 0 1 4 4v10a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V7a4 4 0 0 1 4-4Zm5 5.5a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7ZM17 6.5h.01',
    facebook: 'M14 8h3V4.5h-3a4 4 0 0 0-4 4V11H7.5v3.5H10V21h3.5v-6.5H16l.5-3.5h-3V8.5a.5.5 0 0 1 .5-.5Z',
    linkedin: 'M7 10v8M7 6.5h.01M11 18v-4.5a2.5 2.5 0 0 1 5 0V18M4 4h16v16H4z',
    twitter: 'M4 4l7 9.5M20 4l-7.5 8.5M4 20l7-8M20 20l-8-11',
    youtube: 'M3 8a3 3 0 0 1 3-3h12a3 3 0 0 1 3 3v8a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V8Zm7 1 5 3-5 3V9Z',
    link: 'M10 13a4 4 0 0 0 5.7.4l3-3a4 4 0 0 0-5.7-5.7l-1.4 1.4M14 11a4 4 0 0 0-5.7-.4l-3 3a4 4 0 0 0 5.7 5.7l1.4-1.4',
};

const d = computed(() => paths[props.name] ?? paths.info);
</script>

<template>
    <svg
        :width="size"
        :height="size"
        viewBox="0 0 24 24"
        :fill="filled ? 'currentColor' : 'none'"
        :stroke="filled ? 'none' : 'currentColor'"
        :stroke-width="strokeWidth"
        stroke-linecap="round"
        stroke-linejoin="round"
        aria-hidden="true"
        focusable="false"
        class="shrink-0"
    >
        <path :d="d" />
    </svg>
</template>
