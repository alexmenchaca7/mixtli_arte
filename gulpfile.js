import path from 'path';
// Módulo nativo de Node.js que permite manejar y manipular rutas de archivos y directorios de manera segura.
// Ejemplo: Convertir rutas relativas a absolutas o trabajar con diferentes sistemas operativos.

import fs from 'fs'; 
// Módulo nativo de Node.js para trabajar con el sistema de archivos.
// Se utiliza para leer, escribir, crear y manipular archivos o directorios.

import { glob } from 'glob';
// Módulo para buscar archivos y directorios basados en patrones (como '*.js' o '**/*.scss').
// Es útil para localizar múltiples archivos dentro de una estructura de carpetas.

import {src, dest, watch, series, parallel} from 'gulp';
// Funciones principales de Gulp:
// - `src`: Selecciona los archivos de origen que serán procesados.
// - `dest`: Define la carpeta de destino donde se guardarán los archivos procesados.
// - `watch`: Observa cambios en archivos y ejecuta tareas automáticamente.
// - `series`: Ejecuta tareas en orden secuencial.

import * as dartSass from 'sass'; 
// Compilador oficial de SASS escrito en Dart, utilizado para convertir código SCSS/SASS en CSS.

import gulpSass from 'gulp-sass'; 
// Plugin de Gulp que conecta el compilador SASS (como dartSass) con el flujo de trabajo de Gulp.
// Permite procesar archivos SCSS dentro de tareas automatizadas.

import cleanCSS from 'gulp-clean-css'; 
// Plugin de Gulp para minificar archivos CSS.
// Elimina espacios, comentarios y optimiza las reglas para reducir el tamaño del archivo.

import terser from 'gulp-terser'; 
// Plugin de Gulp para minificar y optimizar archivos JavaScript.
// Reduce el tamaño de los archivos JS eliminando espacios, comentarios y aplicando técnicas de compresión.

import concat from 'gulp-concat';

import sharp from 'sharp'; 
// Librería avanzada de procesamiento de imágenes en Node.js.
// Permite redimensionar, convertir formatos (como JPEG a WebP o a AVIF), ajustar calidad y otras operaciones con imágenes.

import svgmin from 'gulp-svgmin'; // Importa el plugin para optimizar SVG

import rev from 'gulp-rev'; // Plugin de Gulp para agregar un hash único a los nombres de archivos, útil para el versionado de archivos estáticos.

import { deleteAsync } from 'del'; // Módulo para eliminar archivos y directorios de manera asíncrona.


const sass = gulpSass(dartSass); // Combinando gulp-sass con el compilador oficial dartSass para procesar SCSS correctamente


// --- TAREAS DE LIMPIEZA ---
// Limpia solo la carpeta de CSS
function cleanCss() {
    return deleteAsync('public/build/css');
}
// Limpia solo la carpeta de JS
function cleanJs() {
    return deleteAsync('public/build/js');
}
// Limpia la carpeta de imágenes (opcional, pero bueno para consistencia)
function cleanImg() {
    return deleteAsync('public/build/img');
}
// Tarea principal que limpia todo al inicio
function cleanAll() {
    return deleteAsync('public/build');
}


// --- TAREAS DE COMPILACIÓN CON MANIFIESTO UNIFICADO ---
export function css() {
    return src('src/scss/app.scss', { sourcemaps: true })
        .pipe(sass().on('error', sass.logError))
        .pipe(cleanCSS())
        .pipe(rev())
        .pipe(dest('./public/build/css', { sourcemaps: '.' }))
        .pipe(rev.manifest('public/build/rev-manifest.json', {
            base: 'public/build',
            merge: true // Une este manifiesto con el existente
        }))
        .pipe(dest('public/build')); // Guarda el manifiesto en la raíz de 'build'
}

export function js() {
    return src('src/js/**/*.js')
        .pipe(concat('app.js'))
        .pipe(terser())
        .pipe(rev())
        .pipe(dest('./public/build/js'))
        .pipe(rev.manifest('public/build/rev-manifest.json', {
            base: 'public/build',
            merge: true // Une este manifiesto con el existente
        }))
        .pipe(dest('public/build')); // Guarda el manifiesto en la raíz de 'build'
}

export async function imagenes(done) {
    const srcDir = './src/img';
    const buildDir = './public/build/img';
    if (!fs.existsSync(buildDir)) {
        fs.mkdirSync(buildDir, { recursive: true });
    }
    const images = await glob('./src/img/**/*{jpg,png,svg}');
    images.forEach(file => {
        const relativePath = path.relative(srcDir, path.dirname(file));
        const outputSubDir = path.join(buildDir, relativePath);
        if (path.extname(file).toLowerCase() === '.svg') {
            procesarSVG(file, outputSubDir);
        } else {
            procesarImagenes(file, outputSubDir);
        }
    });
    done();
}


// // Tarea para optimizar las imagenes redimensionandolas con Sharp
// export async function crop(done) {
//     const inputFolder = 'src/img/gallery/full'; // Carpeta donde están las imágenes originales
//     const outputFolder = 'src/img/gallery/thumb'; // Carpeta donde se guardarán las imágenes redimensionadas
//     const width = 250; // Ancho al que se redimensionarán las imágenes
//     const height = 180; // Alto al que se redimensionarán las imágenes

//     // Generando la carpeta de salida (thumb) si no existe
//     if (!fs.existsSync(outputFolder)) {
//         fs.mkdirSync(outputFolder, { recursive: true });
//     }

//     // Filtrando los archivos seleccionando solo aquellos con extensión .jpg
//     const images = fs.readdirSync(inputFolder).filter(file => {
//         return /\.(jpg)$/i.test(path.extname(file));
//     });
//     try {
//         // Procesando cada una de las imagenes
//         images.forEach(file => {
//             const inputFile = path.join(inputFolder, file); // Ruta completa del archivo de entrada
//             const outputFile = path.join(outputFolder, file); // Ruta completa del archivo de salida
//             sharp(inputFile) 
//                 .resize(width, height, { 
//                     position: 'centre' 
//                 })
//                 .toFile(outputFile); // Guardando la imagen procesada en la carpeta de salida
//         });

//         done();
//     } catch (error) {
//         console.log(error);
//     }
// }


// Función auxiliar para optimizar y copiar archivos SVG
function procesarSVG(file, outputSubDir) {
    
    // Creando el directorio de salida si no existe
    if (!fs.existsSync(outputSubDir)) {
        fs.mkdirSync(outputSubDir, { recursive: true }); // Creando todos los subdirectorios necesarios
    }

    const baseName = path.basename(file, path.extname(file)); // Obteniendo el nombre base del archivo sin la extensión
    const outputFile = path.join(outputSubDir, `${baseName}.svg`); // Ruta del archivo SVG optimizado

    // Procesando y optimizando el archivo SVG
    src(file)
        .pipe(svgmin()) // Optimiza el SVG
        .pipe(dest(outputSubDir)); // Guarda el SVG optimizado en la carpeta de salida
}


// Función auxiliar de la tarea de imagenes para procesar las imágenes
function procesarImagenes(file, outputSubDir) {

    // Creando el directorio de salida si no existe
    if (!fs.existsSync(outputSubDir)) {
        fs.mkdirSync(outputSubDir, { recursive: true }); // Creando todos los subdirectorios necesarios
    }

    const baseName = path.basename(file, path.extname(file)); // Obteniendo el nombre base del archivo sin la extensión
    const extName = path.extname(file); // Obteniendo la extensión del archivo (ej: .jpg, .png)

    // Defiendo las rutas de salida para los archivos procesados
    const outputFile = path.join(outputSubDir, `${baseName}${extName}`); // Archivo optimizado con la misma extensión
    const outputFileWebp = path.join(outputSubDir, `${baseName}.webp`); // Archivo en formato WebP
    const outputFileAvif = path.join(outputSubDir, `${baseName}.avif`); // Archivo en formato Avif

    // Configurando la calidad de las imágenes procesadas
    const options = { quality: 80 };
    
    // Si el archivo es un PNG, asegurarse de que la transparencia se mantenga
    if (extName.toLowerCase() === '.png') {
        // Procesando y guardando la imagen en formato PNG optimizado
        sharp(file)
            .png({ quality: 80, compressionLevel: 9 }) // Optimización para PNG sin perder transparencia
            .toFile(outputFile); // Guardar el archivo optimizado en su formato original (PNG)

        // Procesando y guardando la imagen en formato WebP
        sharp(file)
            .webp(options) // Configuración para WebP
            .toFile(outputFileWebp); // Guardar como WebP

        // Procesando y guardando la imagen en formato Avif
        sharp(file)
            .avif(options) // Configuración para AVIF
            .toFile(outputFileAvif); // Guardar como AVIF
    } else {
        // Si el archivo no es un PNG (es JPG o JPEG), se optimiza en formato JPEG y además en WebP y AVIF
        sharp(file)
            .jpeg(options) // Configuración para JPEG
            .toFile(outputFile); // Guardar como JPEG optimizado

        // Procesando y guardando la imagen en formato WebP
        sharp(file)
            .webp(options) // Configuración para WebP
            .toFile(outputFileWebp); // Guardar como WebP

        // Procesando y guardando la imagen en formato Avif
        sharp(file)
            .avif(options) // Configuración para AVIF
            .toFile(outputFileAvif); // Guardar como AVIF
    }
}


// ===== TAREA WATCH CORREGIDA Y DEFINITIVA =====
export function dev() {
    watch('src/scss/**/*.scss', series(cleanCss, css)); // Limpia CSS y luego compila
    watch('src/js/**/*.js', series(cleanJs, js));         // Limpia JS y luego compila
    watch('src/img/**/*.{png,jpg}', series(cleanImg, imagenes)); // Limpia imágenes y luego las procesa
}

const build = parallel(series(js, css), imagenes);

// ===== FLUJO DE TRABAJO FINAL Y DEFINITIVO =====
export default series(
    cleanAll, // 1. Primero, limpia todo.
    build,    // 2. Luego, ejecuta la compilación en el orden correcto.
    dev       // 3. Finalmente, empieza a observar los cambios.
);