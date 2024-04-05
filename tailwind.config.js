/** @type {import('tailwindcss').Config} */
import colors from 'tailwindcss/colors'
import forms from '@tailwindcss/forms'
import typography from '@tailwindcss/typography'

export default {
  content: [
      './resources/**/*.blade.php',
      './vendor/filament/**/*.blade.php',
  ],
  theme: {
    extend: {
        colors: {
            primary: colors.amber,
            danger: colors.red,
            success: colors.green,
            warning: colors.amber,
            transparent: 'transparent',
            gray: colors.gray,
            red: colors.red,
            orange: colors.orange,
            yellow: colors.amber,
            lime: colors.lime,
            green: colors.emerald,
            cyan: colors.cyan,
            blue: colors.blue,
            indigo: colors.indigo,
            purple: colors.purple,
            pink: colors.pink,
            black: colors.black,
            white: colors.white,
        },
    },
  },
    plugins: [
        forms,
        typography,
    ],
}

