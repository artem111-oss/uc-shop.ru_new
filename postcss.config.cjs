// PostCSS configuration with PurgeCSS for removing unused CSS
const purgecss = require('@fullhuman/postcss-purgecss').default;

module.exports = {
  plugins: [
    purgecss({
      // Scan these files to find used CSS classes
      content: [
        './resources/**/*.blade.php',
        './resources/**/*.vue',
        './resources/**/*.ts',
        './resources/**/*.js',
        './public/**/*.html'
      ],
      
      // Preserve these classes even if not found in content files
      safelist: {
        standard: [
          // Toast notification classes
          'uc-toast',
          'uc-toast--show',
          'uc-toast--error',
          'uc-toast--success',
          'uc-toast--info',
          'uc-toast__icon',
          'uc-toast__message',
          
          // Modal classes (только используемые)
          'modal-overlay',
          'modal-content',
          'show',
          'close-btn',
          'ok-btn',
          
          // Form validation
          'is-invalid',
          'is-valid',
          'is-selected',
          
          // Bootstrap минимум
          'active',
          'disabled',
          'form-control',
          'btn-primary',
          'text-danger',
          
          // Offcanvas (используется в app.ts)
          'offcanvas',
          
          // Vue
          'payment-game'
        ],
        deep: [],
        greedy: []
      },
      
      // Extract class names from dynamic attributes
      defaultExtractor: content => {
        // Match Blade syntax: {{ $class }}, @class(), class="dynamic-{{ $var }}"
        // Match Vue syntax: :class="dynamic", v-bind:class
        // Match standard: class="static-class"
        const broadMatches = content.match(/[^<>"'`\s]*[^<>"'`\s:]/g) || [];
        const innerMatches = content.match(/[^<>"'`\s.()]*[^<>"'`\s.():]/g) || [];
        return broadMatches.concat(innerMatches);
      },
      
      // Remove CSS comments and minify
      rejected: false,
      rejectedCss: false
    })
  ]
};
