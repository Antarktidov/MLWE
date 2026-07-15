// vite.config.js
import { defineConfig } from "file:///d:/GitHub/MLWE/node_modules/vite/dist/node/index.js";
import laravel from "file:///d:/GitHub/MLWE/node_modules/laravel-vite-plugin/dist/index.js";
import { svelte } from "file:///d:/GitHub/MLWE/node_modules/@sveltejs/vite-plugin-svelte/src/index.js";
var vite_config_default = defineConfig({
  plugins: [
    laravel({
      input: ["resources/js/app.js"],
      refresh: true
    }),
    svelte({
      compilerOptions: {
        compatibility: {
          componentApi: 4
        }
      }
    })
  ]
});
export {
  vite_config_default as default
};
//# sourceMappingURL=data:application/json;base64,ewogICJ2ZXJzaW9uIjogMywKICAic291cmNlcyI6IFsidml0ZS5jb25maWcuanMiXSwKICAic291cmNlc0NvbnRlbnQiOiBbImNvbnN0IF9fdml0ZV9pbmplY3RlZF9vcmlnaW5hbF9kaXJuYW1lID0gXCJkOlxcXFxHaXRIdWJcXFxcTUxXRVwiO2NvbnN0IF9fdml0ZV9pbmplY3RlZF9vcmlnaW5hbF9maWxlbmFtZSA9IFwiZDpcXFxcR2l0SHViXFxcXE1MV0VcXFxcdml0ZS5jb25maWcuanNcIjtjb25zdCBfX3ZpdGVfaW5qZWN0ZWRfb3JpZ2luYWxfaW1wb3J0X21ldGFfdXJsID0gXCJmaWxlOi8vL2Q6L0dpdEh1Yi9NTFdFL3ZpdGUuY29uZmlnLmpzXCI7aW1wb3J0IHsgZGVmaW5lQ29uZmlnIH0gZnJvbSAndml0ZSc7XG5pbXBvcnQgbGFyYXZlbCBmcm9tICdsYXJhdmVsLXZpdGUtcGx1Z2luJztcbmltcG9ydCB7IHN2ZWx0ZSB9IGZyb20gJ0BzdmVsdGVqcy92aXRlLXBsdWdpbi1zdmVsdGUnO1xuXG5leHBvcnQgZGVmYXVsdCBkZWZpbmVDb25maWcoe1xuICAgIHBsdWdpbnM6IFtcbiAgICAgICAgbGFyYXZlbCh7XG4gICAgICAgICAgICBpbnB1dDogWydyZXNvdXJjZXMvanMvYXBwLmpzJ10sXG4gICAgICAgICAgICByZWZyZXNoOiB0cnVlLFxuICAgICAgICB9KSxcbiAgICAgICAgc3ZlbHRlKHtcbiAgICAgICAgICAgIGNvbXBpbGVyT3B0aW9uczoge1xuICAgICAgICAgICAgICAgIGNvbXBhdGliaWxpdHk6IHtcbiAgICAgICAgICAgICAgICAgICAgY29tcG9uZW50QXBpOiA0XG4gICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgfVxuICAgICAgICB9KSxcbiAgICBdLFxufSk7Il0sCiAgIm1hcHBpbmdzIjogIjtBQUFrTyxTQUFTLG9CQUFvQjtBQUMvUCxPQUFPLGFBQWE7QUFDcEIsU0FBUyxjQUFjO0FBRXZCLElBQU8sc0JBQVEsYUFBYTtBQUFBLEVBQ3hCLFNBQVM7QUFBQSxJQUNMLFFBQVE7QUFBQSxNQUNKLE9BQU8sQ0FBQyxxQkFBcUI7QUFBQSxNQUM3QixTQUFTO0FBQUEsSUFDYixDQUFDO0FBQUEsSUFDRCxPQUFPO0FBQUEsTUFDSCxpQkFBaUI7QUFBQSxRQUNiLGVBQWU7QUFBQSxVQUNYLGNBQWM7QUFBQSxRQUNsQjtBQUFBLE1BQ0o7QUFBQSxJQUNKLENBQUM7QUFBQSxFQUNMO0FBQ0osQ0FBQzsiLAogICJuYW1lcyI6IFtdCn0K
