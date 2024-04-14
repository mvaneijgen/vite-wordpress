import { defineConfig } from "vite";
import { rm, mkdir, writeFile } from "node:fs/promises";
import { resolve } from "path";

const __dirname = new URL(".", import.meta.url).pathname;

export default defineConfig({
  css: {
    devSourcemap: true,
  },
  server: {
    port: 3000,
    host: "0.0.0.0",
  },
  build: {
    manifest: "manifest.json",
    assetsDir: ".",
    outDir: `dist`,
    sourcemap: true,
    emptyOutDir: true,
    rollupOptions: {
      input: ["assets/scripts/main.js", "assets/styles/main.scss"],
      output: {
        entryFileNames: "scripts/[name].[ext]",
        assetFileNames: "styles/[name].[ext]",
      },
    },
  },
  plugins: [
    {
      name: "Cleaning theme folder",
      async buildStart() {
        await rm(resolve(__dirname, "../dist/js"), {
          recursive: true,
          force: true,
        });
        await rm(resolve(__dirname, "../dist/styles"), {
          recursive: true,
          force: true,
        });
        await mkdir(resolve(__dirname, "../dist/js"), { recursive: true });
        await mkdir(resolve(__dirname, "../dist/styles"), { recursive: true });
      },
    },
    {
      name: "php",
      handleHotUpdate({ file, server }) {
        if (file.endsWith(".php")) {
          server.ws.send({ type: "full-reload" });
        }
      },
    },
  ],
});
