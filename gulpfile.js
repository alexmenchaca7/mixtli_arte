import path from 'path';
// Módulo nativo de Node.js que permite manejar y manipular rutas de archivos y directorios de manera segura.
// Ejemplo: Convertir rutas relativas a absolutas o trabajar con diferentes sistemas operativos.

import fs from 'fs'; 
// Módulo nativo de Node.js para trabajar con el sistema de archivos.
// Se utiliza para leer, escribir, crear y manipular archivos o directorios.

import { glob } from 'glob';
// Módulo para buscar archivos y directorios basados en patrones (como '*.js' o '**/*.scss').
// Es útil para localizar múltiples archivos dentro de una estructura de carpetas.

import {src, dest, watch, series} from 'gulp';
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

const sass = gulpSass(dartSass); // Combinando gulp-sass con el compilador oficial dartSass para procesar SCSS correctamente




// Tarea para procesar y minificar JavaScript
export function js(done){
    src('src/js/**/*.js') // Selecciona todos los archivos .js dentro de src/js y sus subcarpetas
        .pipe(concat('app.js')) // Combina todos los archivos en uno solo
        .pipe(terser()) // Minifica el archivo combinado
        .pipe(dest('build/js')); // Guarda el resultado en build/js/
    done();
}


// Tarea para procesar y minificar CSS
export function css(done) {
    src('src/scss/app.scss', {sourcemaps: true})
        .pipe(sass().on('error', sass.logError)) // Compila SASS y si hay un error lo muestra en la terminal
        .pipe(cleanCSS())
        .pipe(dest('build/css', {sourcemaps: '.'})) // Guarda los archivos en la carpeta de destino
        
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


// Tarea para optimizar las imagenes originales generando su versión JPEG y WebP
export async function imagenes(done) {
    const srcDir = './src/img'; // Carpeta donde están las imágenes originales
    const buildDir = './build/img'; // Carpeta donde se guardarán las nuevas imágenes

    // Usando glob para encontrar todas las imágenes en el directorio de origen con extensiones .jpg o .png
    const images =  await glob('./src/img/**/*{jpg,png}')

    // Procesando cada imagen encontrada
    images.forEach(file => {
        const relativePath = path.relative(srcDir, path.dirname(file)); // Obteniendo la ruta relativa desde srcDir
        const outputSubDir = path.join(buildDir, relativePath); // Creando la ruta de salida correspondiente en buildDir

        // Llamando a la función para procesar las imágenes
        procesarImagenes(file, outputSubDir);
    });
    done();
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

    // Procesando y guardando la imagen optimizada en su formato original
    sharp(file).jpeg(options).toFile(outputFile);

    // Procesando y guardando la imagen en formato WebP
    sharp(file).webp(options).toFile(outputFileWebp);

    // Procesando y guardando la imagen en formato Avif
    sharp(file).avif().toFile(outputFileAvif);
}


// Tarea para observar cambios en los archivos
export function dev() { // No se pasa la función de done porque es un watch
    watch('src/scss/**/*.scss', css); // Aqui se observan todos los archivos con extensión .scss y cuando hayan cambios ejcuta la función de css
    watch('src/js/**/*.js', js); // Aqui se observan todos los archivos con extensión .js y cuando hayan cambios ejcuta la función de js
    watch('src/img/**/*.{png,jpg}', imagenes); // Aqui se observan todos los archivos con extensión .png y .jpg y cuando hayan cambios ejcuta la función de imagenes
}


// Flujo de trabajo por defecto
export default series(js, css, imagenes, dev);