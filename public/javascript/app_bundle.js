/******/ (function(modules) { // webpackBootstrap
/******/ 	// install a JSONP callback for chunk loading
/******/ 	function webpackJsonpCallback(data) {
/******/ 		var chunkIds = data[0];
/******/ 		var moreModules = data[1];
/******/ 		var executeModules = data[2];
/******/
/******/ 		// add "moreModules" to the modules object,
/******/ 		// then flag all "chunkIds" as loaded and fire callback
/******/ 		var moduleId, chunkId, i = 0, resolves = [];
/******/ 		for(;i < chunkIds.length; i++) {
/******/ 			chunkId = chunkIds[i];
/******/ 			if(Object.prototype.hasOwnProperty.call(installedChunks, chunkId) && installedChunks[chunkId]) {
/******/ 				resolves.push(installedChunks[chunkId][0]);
/******/ 			}
/******/ 			installedChunks[chunkId] = 0;
/******/ 		}
/******/ 		for(moduleId in moreModules) {
/******/ 			if(Object.prototype.hasOwnProperty.call(moreModules, moduleId)) {
/******/ 				modules[moduleId] = moreModules[moduleId];
/******/ 			}
/******/ 		}
/******/ 		if(parentJsonpFunction) parentJsonpFunction(data);
/******/
/******/ 		while(resolves.length) {
/******/ 			resolves.shift()();
/******/ 		}
/******/
/******/ 		// add entry modules from loaded chunk to deferred list
/******/ 		deferredModules.push.apply(deferredModules, executeModules || []);
/******/
/******/ 		// run deferred modules when all chunks ready
/******/ 		return checkDeferredModules();
/******/ 	};
/******/ 	function checkDeferredModules() {
/******/ 		var result;
/******/ 		for(var i = 0; i < deferredModules.length; i++) {
/******/ 			var deferredModule = deferredModules[i];
/******/ 			var fulfilled = true;
/******/ 			for(var j = 1; j < deferredModule.length; j++) {
/******/ 				var depId = deferredModule[j];
/******/ 				if(installedChunks[depId] !== 0) fulfilled = false;
/******/ 			}
/******/ 			if(fulfilled) {
/******/ 				deferredModules.splice(i--, 1);
/******/ 				result = __webpack_require__(__webpack_require__.s = deferredModule[0]);
/******/ 			}
/******/ 		}
/******/
/******/ 		return result;
/******/ 	}
/******/
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// object to store loaded and loading chunks
/******/ 	// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 	// Promise = chunk loading, 0 = chunk loaded
/******/ 	var installedChunks = {
/******/ 		"app": 0
/******/ 	};
/******/
/******/ 	var deferredModules = [];
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "/";
/******/
/******/ 	var jsonpArray = window["webpackJsonp"] = window["webpackJsonp"] || [];
/******/ 	var oldJsonpFunction = jsonpArray.push.bind(jsonpArray);
/******/ 	jsonpArray.push = webpackJsonpCallback;
/******/ 	jsonpArray = jsonpArray.slice();
/******/ 	for(var i = 0; i < jsonpArray.length; i++) webpackJsonpCallback(jsonpArray[i]);
/******/ 	var parentJsonpFunction = oldJsonpFunction;
/******/
/******/
/******/ 	// add entry module to deferred list
/******/ 	deferredModules.push(["./assets/javascript/App.jsx","vendor"]);
/******/ 	// run deferred modules when ready
/******/ 	return checkDeferredModules();
/******/ })
/************************************************************************/
/******/ ({

/***/ "./assets/fonts/Emulogic.woff2":
/*!*************************************!*\
  !*** ./assets/fonts/Emulogic.woff2 ***!
  \*************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony default export */ __webpack_exports__["default"] = ("data:application/font-woff;base64,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");

/***/ }),

/***/ "./assets/fonts/PixelEmulator.woff":
/*!*****************************************!*\
  !*** ./assets/fonts/PixelEmulator.woff ***!
  \*****************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony default export */ __webpack_exports__["default"] = ("data:application/font-woff;base64,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");

/***/ }),

/***/ "./assets/fonts/YosterIslandRegular.woff2":
/*!************************************************!*\
  !*** ./assets/fonts/YosterIslandRegular.woff2 ***!
  \************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony default export */ __webpack_exports__["default"] = ("data:application/font-woff;base64,d09GMgABAAAAAAxEAA0AAAAAMIgAAAvwAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAGiQcUgZWAIQuEQgKx0C5BQuBfAABNgIkA4NuBCAFiGAHghwbaiczAzHGgY2B7ET2f0pujCE9KlkFJQpt2xuGbeQksrB9Qje7GTyhsmto97mY+sJMkH4yKyMm6oxe9eqwfMLLKH64SkUUtKggsoNI8F/t7Xduz2zqntldQgpCRhVVevvTq+IR8uJDFg5hKIxEIZEIg0UYVAo/3M/eojDj/cjCBcU1DvQQqkwGfnbABc8/h7/724IP/NMekzqRLluaTe1k0sA5F4hDW6POtCqeoeZGGxxmBoU2aSaEmmg7vMSm/zzh797dx0wRIlEKUpZsQqU3PO5vx0pIJCXMEwk7cfqnIiKiat7UrMH/AQjGP5R0gQHQ9nYAuJ8B8Gb9/9b6tF0fSBGoUIdYGELnt/vdqjdUXf/z2d+Z0ws0+8NQ1VNzTv/ZZRUCBSRz1oZAIQi9NiwMChPh4mJErIvL/lDtn+weumHn2R3bLyUyUShRsY5yDuHPedgjUMBWgDHylMIzW2cyMCDgtpOV7tPeKDcy4P+/DvTea5S4AQKSZSBDsnbzmWXPnKsqkUp9q+bS0Ec6wAAGkbRWTW8whK+QCL7whcdWxUhOtrL7H192hLkH+qcASFffva1KAF1UCGxVTQFOQFXbAdUeHFXmRs7eOxZPZfGFcrVWb3Vy58mHv4xxjiSy/xpQ7MQS6Qo5Re6IxBxJVGOTY/iTEc7+AdLx/6upyiJPmb+dynBTIdq1aa0gJyU+BTimQXpvhhZQ069xbgr0IdLU68NghdEWpTLezw3qH4k8/GFxIEFSNMNyvGAjSrKiarphWrbjej6oQK2JkzTLC7PFarM7nC63x+vzB4KhcCQaiyeSZMjmqTRotTvQ7fcGwHAEjCcwnc1prmCTy5ViJV0gMYdSgjLU6tVmlbT+M+x8Ln4Fn/vKm9H6CXKqKOWqOZ+0bkaEWU9pwkb0wnRqmozd5mNjthYy+nCqEhYMKKDJLtGEkDsJWiApFsoB/wuy5+Hw6AccPhh7pew5r+XcfqKK1eBaImffCGwMOB3Y7sPn35W3rQppuH42H1Wvby3asL/W09KqGegb2Arm6VZmjLSm74zu8+xzB96d6Qdv5LXFmhX113s5h1ON0bMdCiH3M1w7zilnzMAeZkyXUd+whwEQihxSL9N9a2StH2M4iPw+1Ku5CxzHUywFPqkrFu+Ch/E4UvHjB06RS0hDDkNVwTNqcTOkkIDmKdzUBO4bftFU1MEjDMYgqrRzwXP0PUHH0O2x8O6nmbybYR2PlMzHsTqvhMWQpZiJldLYy6Z2wngCb9pej7AccAMLRepslZBNYUWVy1CTBSO1kjCho7U8xumlMNmQgMB5jEWVtBVqSiqpWSH4knQJW+uhzy3QVMI9bfCWxcv4QUm7FIQ7RWbz6CcMiLHRVQpIARUUvFVBLGnFZZ0DUTTjgauAQVlILnc+5EqmYAlnLYiBpA2WFDIKk9QJIpZX7QmlCbHyPd7PVRHRpykyTczICaL0PBR0EFZNERWVjxKBEWGyRsolzZ5kxCooqwzGDMTFgpCPVmwigmJ6qWNnDuswll0as0Db7EdQ/K3o5KNK/Vw3YVbML2UJRc8JYGXJQibxAMh7gyOvWGs9N0VGuLgIEZ2OZHe6GedoRyGhN7FIVXfUvGFdtEJNYhRDxXa/1rwEzaVWqAjQyGTWGiCJndHzGW9Otwqq0gdNOuYF9yNSmXahsbdGjDXFeU1kP/OCFBl4arLWcVdu2mSJCZI6Pq0iYxe9iasyJ2WqZKprAU3yeQhPLITbgMiKq6Tdku7CdMtnoZy1Sy7XDEdapRKwwkg/Ls82CMBa2Zp0A7s2X1BRtxGCwToog54waWsZspoTmDx0ZT4ngd9FeRXLLdwIQIl3fNiVJZnHx+oB1Qrn+qztjC5rcuDoDhCFuLv4+8T+3AcYRxNtcVYilCtX/qeErb5+/Lqz6otu1C/PYP4qm4r86lYjpbxAPHUrRoR1OZhPQHLUmYZExd/Npu7k1xdODaurnyY2aarIYGWsvk9imlrDNhGt2XtAoo3LzGtdd7cqdKkDN1L/2sGRcPFmw/H42EX4UgjctLHru/ECtBCLVd5B72LLgf0dEN6TzCvNLIhTbTrTtBybecK9i3XrpFuWLdHaGtdpN2rhZIYXBT55rCWkxpWYb4e/dldt5qlQ53vjaPXiXe6PMoK24lfdi1+cG/mRj0q3CIR86qwoILf3U+PiKAhk1k+LZCphBMZ4fQ56PYI9FLt9IHcFFuop8aMuFGdGVmWw79dU9wEzFrw8GUMcR+5wiylti+EGeGkSYJjevjV7vCHVOS9AAApQBImwRBr6QTSaDoRaHY/yhNdHESY9EFWb1ZEdpujucG0DtaxTS6mlnZUEH0OWiMxqw5Gh8JanG1EVTq4ITtB75kDBWV9wQ1RQy8SyFvUhQmupn8w/GX7ZSSzrshOR8DkcmBZ4rOQ/Ebc+23rvV9Kr/+iDd+PgqLpXu8caM1xdzIf9zG7uvKG0lKwSe1p4YUu493EuNc8IHJ4zUiT+nOw773fMlRcsSHpwMznhRRsUWRXTrKT2yvo0V6kdL9zcrWF47GeIivfI1o+nlrCwAvmWI25QiFCVaGYcchCkrPtglRTmiE33t09yhRyIdG23tGbT0J+583rQWTc3gwMSxQt08yIxBrLU+okYAqclUG4CCfa2UJi7sDXNggtZift9wnv66wx1TXpMzoSJ6ME1uYTdOfWU4bSa7+TvHG+uCgGBglBUgKEEC4ECwuOuQCZrAREqBRbChIQeTpWqd94LIExWwOAWAi/Gx3slEE7UaD1IQLc2W7J/RILcs8rVkltE387ckeDYsSb33SZcxAMoMccXbbe8a7X7rExKORCOIUBAWMO41Ult66gZB8My40K1qoQugnvSy1TraRtalml6DtS1imBsRSAB260zNScopAdgghA+WJzEOzw7TEGR9wc/DjdYJGOINxvAdRF355/T60NEUNcCH3oJ/MsVx2gy2dJ5EggS93XZqZxn5lG9vGQP9OcDBhQBCUCeeNiugyP3QrXrKqm2SdZtiE3TgfwQJj+op9glfyGU64mzwLf4dAsmrQz88OHmsQAQWVsN0h96hbk/uDMgnbkDwKF/rHt5ETdVcELXKazuua6IVhnh9WjOy+8SS13eCZVWsdsKIDFEJXXuucRYQwaIeDEpc5l4vS9oZuT5zSylx1qLuMnVwf9c5kb5GkqEdT27sXpRot40HwuuURWWzpqu92vCJxDZnHlJ1L3tnVPcfqd8nIJbdNkvtci+XQE1Qtdj3vsEgrDxqAqJ7TzvZbkteOhIoDrh4PJOQwEb1GZC7iI8SaCn7bXtvOjCST+zayzCr+C1y/bc7m2QDfSsm84SA2rXubtv+qOy1/6ZOmvI7nX76zb0ya+lvv4S/Oxu7VeeGYD3t4q71/x//v8EYMgY+CXwyTkK64gHADTUn7ToPbiYZV5NvY1j+qItupz2/XXDeTZ00tQIzzdB79B1PS+2oa0n+zDsE1oDtBQOU37yd/egNDqpqz27l6YBT9o/vMhuRXcrdYP3Yu8BmwI8l3WN37hBuHTKcKatOQeS+nk8hbLZNjRjd8NkwTLMDjuFPZv9XNu3PaOsq++LD6HUXAnNIA/C5Ehuw2xd7sKemncv0zfJBzBNEufzCwTzRSCY7zEgmK8XCBb7BGHPxp4imO836FHcAAAAAAAAAAAAAHD9g7GZ2t3QyvD24migb96sjk208NXFO2HS6Sy6XDXre0rE2yn5VMCygYkp68ZiZaJLNargkmWlYbYVEQ24oG6ErdxyHhKhJ/fcBN29LTqFSxG1t4D/QLAQX8Og0m/aoA/XwFT4S4ri3yFp/abJyvtv5V+3hf8HxW2UvgpX32S6A3+tq+YfVdtE8Cn9ybADQ6S6JbLttHTFVQ60ayA1vwX7akPVDURcRM2/TNLZsnkYmCF5/ntN1V86/X9hxFVjE1NtXT19A0MjYxNTM3MLS4hAojFYHJ5AJJEpVBqdwWSxOVweXyAUiSVSOQsrGzsHJxc3Dy8fv4CgkLCIqJi4hKSUtIysnLyCopInH1BVPwifDCYH5Y8vQLvT1uk+/cDI0NjcwsbWwdHJqzfPXtx6AAA=");

/***/ }),

/***/ "./assets/fonts/prstart.woff":
/*!***********************************!*\
  !*** ./assets/fonts/prstart.woff ***!
  \***********************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony default export */ __webpack_exports__["default"] = ("data:application/font-woff;base64,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");

/***/ }),

/***/ "./assets/fonts/prstartk.woff":
/*!************************************!*\
  !*** ./assets/fonts/prstartk.woff ***!
  \************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony default export */ __webpack_exports__["default"] = ("data:application/font-woff;base64,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");

/***/ }),

/***/ "./assets/images/colophon_welcome.png":
/*!********************************************!*\
  !*** ./assets/images/colophon_welcome.png ***!
  \********************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony default export */ __webpack_exports__["default"] = ("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAMAAAAA0CAMAAADrAcc6AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAABhQTFRF/6NHuPgY////518T+DgAAAAAixcA////euOm3AAAAAh0Uk5T/////////wDeg71ZAAACMElEQVR42txXibLCMAjEBNL//+OXo6naqQrJBsfHqHU9lmwgUGizGu9PjVG1dfzFxWb5Le/cSgeBAuVHWMVvFMCVnw3sRFIfq/gHBHAx9b/sAmz81hRq9IY94lCOQOBV/DYBVPaHMr9oXXDYjdfwGwWUvaHjRfWHECRbFsAr+McEUIuysGr9NQasU2DlHxFA1F18dpB/FrgJKG8IzW8WkDlbW9o3SSGAuwBWCLDy2wXUAFOtK1oBUtOoXBQCjPxDAkpXrS6UOS09oZnx/GNnoFbFJQLM/OMCtPzcG5OurJv57bcS1UVLUVNn5VX85luJzbSgrdZFtmyojb8JEElJnmwWZ7tVk2vD+qOM+ydp/34OH+t/oQDsj1Lqn7TLLC57cgig6/Uj/bUIHN8ArkK3w+hFBID+VgiIff3RTcBjbGdxnsBilRAj0ZszAPJ3ikCSaVxCUBTkF5JPEZj3tyCFJJbFFxFRHFIo7UqeBE7gIqAfghhfVSGcv2cBZwcDuGaQVgAAEyr1LwRcrV/Q/laUUepn4CbufQBShe6NzKMKPRw+OR3GIdzKUMugdymE8nc0snYy7o1mGLc6VJ4xvrmZQ/kjSe0GdQ+PTOPDrteP9kc9Hg/9eQrLB0P7o3MNmcUKAVB/CyYyX4yfyJwxfCLzxvhO7Hz9JwKQE5kzxt8LOePfTyH0hOSN4ROZN6YfTf2FE9l3+8APViH0hOSN8ROZM8ZPZM4YPpF5Y/hE5o3/BBgACz7ema3m3fAAAAAASUVORK5CYII=");

/***/ }),

/***/ "./assets/images/footer_diorama.png":
/*!******************************************!*\
  !*** ./assets/images/footer_diorama.png ***!
  \******************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony default export */ __webpack_exports__["default"] = ("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACgAAAA+CAYAAABZcVnrAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAICSURBVHja7FgxUoQwFM1nGHtbrbyAnYN30NbGRk9gZS/0NnoCKxsvsEeAsdbWSlu7Lbb58uPCQDYJCQtJXPNnssznJfB4yX+BZYjIJmkPV7htX7asf4SWsCni8RqL7yN+5G0d7+eARVFgtx+1vdtn1u2ni5TNEeubv5zkvXxMJFOpd7f/sQFdvObSIav7S2aqYsICj0gwEhyMmyegAuE2YxhUIFQoNNaNguKNKO+ea3IDQrP5ILcZgUDrgwL5FWNoTHayrU7S3s4Y5nluvAXOt9UpQqpgSFWs2kmCIfgvFNymCLAsS2yCiuH3cvIwwiVFMtZm6GZkE1hV1QYoO2eDbz3FdIO6YZZlwGYOWwW5Yi6IjVLQZmpcK+hcORuCbUHIwMVi0cvrjyTtxYZwMYDvgfpp5R1U6gEATibXEq18sPG5QT8c7XsCbuOD2mmd2vda/DgzW4PrAeijKIYIeqtWIx/04XOmCganXJegVUHY+poVLikSICvR+dygkc7sg8D95q8GDmwzTnBVJ27t2QH6xhMl89ND/ZM5wlOp5IrBPvCUEu1AzzjQPPfOlJ9MJ79rPOkBDUhH2UAPeNomqqfyjG9OcWAR/6PeeYLBvyxAdy8EencKLI9rcOcJpuI3Q2h5T0FxVwkhj2swEowEbQhC9QWh5b0XVjoRWh7X4M4T/BFgACMtzIYbX1MTAAAAAElFTkSuQmCC");

/***/ }),

/***/ "./assets/images/icons/questionblock.png":
/*!***********************************************!*\
  !*** ./assets/images/icons/questionblock.png ***!
  \***********************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony default export */ __webpack_exports__["default"] = ("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABoAAAAcCAYAAAB/E6/TAAAACXBIWXMAAAsTAAALEwEAmpwYAAAKT2lDQ1BQaG90b3Nob3AgSUNDIHByb2ZpbGUAAHjanVNnVFPpFj333vRCS4iAlEtvUhUIIFJCi4AUkSYqIQkQSoghodkVUcERRUUEG8igiAOOjoCMFVEsDIoK2AfkIaKOg6OIisr74Xuja9a89+bN/rXXPues852zzwfACAyWSDNRNYAMqUIeEeCDx8TG4eQuQIEKJHAAEAizZCFz/SMBAPh+PDwrIsAHvgABeNMLCADATZvAMByH/w/qQplcAYCEAcB0kThLCIAUAEB6jkKmAEBGAYCdmCZTAKAEAGDLY2LjAFAtAGAnf+bTAICd+Jl7AQBblCEVAaCRACATZYhEAGg7AKzPVopFAFgwABRmS8Q5ANgtADBJV2ZIALC3AMDOEAuyAAgMADBRiIUpAAR7AGDIIyN4AISZABRG8lc88SuuEOcqAAB4mbI8uSQ5RYFbCC1xB1dXLh4ozkkXKxQ2YQJhmkAuwnmZGTKBNA/g88wAAKCRFRHgg/P9eM4Ors7ONo62Dl8t6r8G/yJiYuP+5c+rcEAAAOF0ftH+LC+zGoA7BoBt/qIl7gRoXgugdfeLZrIPQLUAoOnaV/Nw+H48PEWhkLnZ2eXk5NhKxEJbYcpXff5nwl/AV/1s+X48/Pf14L7iJIEyXYFHBPjgwsz0TKUcz5IJhGLc5o9H/LcL//wd0yLESWK5WCoU41EScY5EmozzMqUiiUKSKcUl0v9k4t8s+wM+3zUAsGo+AXuRLahdYwP2SycQWHTA4vcAAPK7b8HUKAgDgGiD4c93/+8//UegJQCAZkmScQAAXkQkLlTKsz/HCAAARKCBKrBBG/TBGCzABhzBBdzBC/xgNoRCJMTCQhBCCmSAHHJgKayCQiiGzbAdKmAv1EAdNMBRaIaTcA4uwlW4Dj1wD/phCJ7BKLyBCQRByAgTYSHaiAFiilgjjggXmYX4IcFIBBKLJCDJiBRRIkuRNUgxUopUIFVIHfI9cgI5h1xGupE7yAAygvyGvEcxlIGyUT3UDLVDuag3GoRGogvQZHQxmo8WoJvQcrQaPYw2oefQq2gP2o8+Q8cwwOgYBzPEbDAuxsNCsTgsCZNjy7EirAyrxhqwVqwDu4n1Y8+xdwQSgUXACTYEd0IgYR5BSFhMWE7YSKggHCQ0EdoJNwkDhFHCJyKTqEu0JroR+cQYYjIxh1hILCPWEo8TLxB7iEPENyQSiUMyJ7mQAkmxpFTSEtJG0m5SI+ksqZs0SBojk8naZGuyBzmULCAryIXkneTD5DPkG+Qh8lsKnWJAcaT4U+IoUspqShnlEOU05QZlmDJBVaOaUt2ooVQRNY9aQq2htlKvUYeoEzR1mjnNgxZJS6WtopXTGmgXaPdpr+h0uhHdlR5Ol9BX0svpR+iX6AP0dwwNhhWDx4hnKBmbGAcYZxl3GK+YTKYZ04sZx1QwNzHrmOeZD5lvVVgqtip8FZHKCpVKlSaVGyovVKmqpqreqgtV81XLVI+pXlN9rkZVM1PjqQnUlqtVqp1Q61MbU2epO6iHqmeob1Q/pH5Z/YkGWcNMw09DpFGgsV/jvMYgC2MZs3gsIWsNq4Z1gTXEJrHN2Xx2KruY/R27iz2qqaE5QzNKM1ezUvOUZj8H45hx+Jx0TgnnKKeX836K3hTvKeIpG6Y0TLkxZVxrqpaXllirSKtRq0frvTau7aedpr1Fu1n7gQ5Bx0onXCdHZ4/OBZ3nU9lT3acKpxZNPTr1ri6qa6UbobtEd79up+6Ynr5egJ5Mb6feeb3n+hx9L/1U/W36p/VHDFgGswwkBtsMzhg8xTVxbzwdL8fb8VFDXcNAQ6VhlWGX4YSRudE8o9VGjUYPjGnGXOMk423GbcajJgYmISZLTepN7ppSTbmmKaY7TDtMx83MzaLN1pk1mz0x1zLnm+eb15vft2BaeFostqi2uGVJsuRaplnutrxuhVo5WaVYVVpds0atna0l1rutu6cRp7lOk06rntZnw7Dxtsm2qbcZsOXYBtuutm22fWFnYhdnt8Wuw+6TvZN9un2N/T0HDYfZDqsdWh1+c7RyFDpWOt6azpzuP33F9JbpL2dYzxDP2DPjthPLKcRpnVOb00dnF2e5c4PziIuJS4LLLpc+Lpsbxt3IveRKdPVxXeF60vWdm7Obwu2o26/uNu5p7ofcn8w0nymeWTNz0MPIQ+BR5dE/C5+VMGvfrH5PQ0+BZ7XnIy9jL5FXrdewt6V3qvdh7xc+9j5yn+M+4zw33jLeWV/MN8C3yLfLT8Nvnl+F30N/I/9k/3r/0QCngCUBZwOJgUGBWwL7+Hp8Ib+OPzrbZfay2e1BjKC5QRVBj4KtguXBrSFoyOyQrSH355jOkc5pDoVQfujW0Adh5mGLw34MJ4WHhVeGP45wiFga0TGXNXfR3ENz30T6RJZE3ptnMU85ry1KNSo+qi5qPNo3ujS6P8YuZlnM1VidWElsSxw5LiquNm5svt/87fOH4p3iC+N7F5gvyF1weaHOwvSFpxapLhIsOpZATIhOOJTwQRAqqBaMJfITdyWOCnnCHcJnIi/RNtGI2ENcKh5O8kgqTXqS7JG8NXkkxTOlLOW5hCepkLxMDUzdmzqeFpp2IG0yPTq9MYOSkZBxQqohTZO2Z+pn5mZ2y6xlhbL+xW6Lty8elQfJa7OQrAVZLQq2QqboVFoo1yoHsmdlV2a/zYnKOZarnivN7cyzytuQN5zvn//tEsIS4ZK2pYZLVy0dWOa9rGo5sjxxedsK4xUFK4ZWBqw8uIq2Km3VT6vtV5eufr0mek1rgV7ByoLBtQFr6wtVCuWFfevc1+1dT1gvWd+1YfqGnRs+FYmKrhTbF5cVf9go3HjlG4dvyr+Z3JS0qavEuWTPZtJm6ebeLZ5bDpaql+aXDm4N2dq0Dd9WtO319kXbL5fNKNu7g7ZDuaO/PLi8ZafJzs07P1SkVPRU+lQ27tLdtWHX+G7R7ht7vPY07NXbW7z3/T7JvttVAVVN1WbVZftJ+7P3P66Jqun4lvttXa1ObXHtxwPSA/0HIw6217nU1R3SPVRSj9Yr60cOxx++/p3vdy0NNg1VjZzG4iNwRHnk6fcJ3/ceDTradox7rOEH0x92HWcdL2pCmvKaRptTmvtbYlu6T8w+0dbq3nr8R9sfD5w0PFl5SvNUyWna6YLTk2fyz4ydlZ19fi753GDborZ752PO32oPb++6EHTh0kX/i+c7vDvOXPK4dPKy2+UTV7hXmq86X23qdOo8/pPTT8e7nLuarrlca7nuer21e2b36RueN87d9L158Rb/1tWeOT3dvfN6b/fF9/XfFt1+cif9zsu72Xcn7q28T7xf9EDtQdlD3YfVP1v+3Njv3H9qwHeg89HcR/cGhYPP/pH1jw9DBY+Zj8uGDYbrnjg+OTniP3L96fynQ89kzyaeF/6i/suuFxYvfvjV69fO0ZjRoZfyl5O/bXyl/erA6xmv28bCxh6+yXgzMV70VvvtwXfcdx3vo98PT+R8IH8o/2j5sfVT0Kf7kxmTk/8EA5jz/GMzLdsAAAAEZ0FNQQAAsY58+1GTAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAACqSURBVHja1JTRDcMgDETPUUYhw2SYMgzLZBh7F+cjoh9OaEFtwLkvBEL28x2QqgIAJNKx+LNCUgKACZ1E/IICQFhvKiAJXYnm0oFsjV58mcg4olYSe69ENt4jq2VJl/vM8TJlGqITojzj3JmV7VS4rtB4j06db58JSh76TV1tGu0k/BK1ptE/UU4dIzb92t2Jfi5Ekoq+PTt1zyOqfS/jid5/lTiPd632AQB4oTMB42PV7AAAAABJRU5ErkJggg==");

/***/ }),

/***/ "./assets/images/twitter_sm.png":
/*!**************************************!*\
  !*** ./assets/images/twitter_sm.png ***!
  \**************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony default export */ __webpack_exports__["default"] = ("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAbhJREFUeNqUk09LG0EYxp+dGbtZY6tpRKtREtGDhATUin+pB3PQD+AX8tZ+mJ56KlSE9qAHQQUll1KrtjaCiSaBLLvZ2XFmF3Z3JC31hWGGd+b5zfO+wxgfa/UR7os9AVHCM8KAcUaJUWEO5/tvzBfFnGXK5P+FkOO37ZRqjrvPHF8UKSWo2vZzDCBDGZSWeVzgzutGG3ufj/Dt4ALvVqdQ2Vr8K0BplJZ0fcAX8Tg4rSOVzQVzMt9rKC3r+j54UFUYzHoZzcl8r1BaCRABLQKkUtH6w/tPmmB5cQIblfkEQIC5sg4uYgI1Y0A6N6MBTr43sb4Zn1Xa0AF6O3ga20sT2tmeDpKA3Z28BrhutdERTxy4qolJQKKErz9uMDyaiQny7ZEEBE3kehPTAxYcL0x8OfwFv3sb7c0WUph7W4hLCB0I7bnKkxaO/4SVDkzqTfzZbqCMpAMJcLiP+46HQYsFyelhG6TvFap1A66nN3Ftaky67QTrpu1BaRmjpH714GbHJXjIomgQivxgCyu5NKhBNMBls4W2YHiwOW6aLpSW9ZtkQSaOz2v2a/2+f3+utEka8sKFRwEGADcq6GEi0lC0AAAAAElFTkSuQmCC");

/***/ }),

/***/ "./assets/javascript/App.jsx":
/*!***********************************!*\
  !*** ./assets/javascript/App.jsx ***!
  \***********************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var react_dom__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! react-dom */ "./node_modules/react-dom/index.js");
/* harmony import */ var react_dom__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(react_dom__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _Colophon_jsx__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./Colophon.jsx */ "./assets/javascript/Colophon.jsx");
/* harmony import */ var _Header_jsx__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./Header.jsx */ "./assets/javascript/Header.jsx");
/* harmony import */ var normalize_css__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! normalize.css */ "./node_modules/normalize.css/normalize.css");
/* harmony import */ var normalize_css__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(normalize_css__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _styles_app_scss__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ../styles/app.scss */ "./assets/styles/app.scss");
/* harmony import */ var _styles_app_scss__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_styles_app_scss__WEBPACK_IMPORTED_MODULE_5__);

 // Components to render


 // Stylesheets that get injected into <head>


 // Grab data-* properties from <header> element and pass them as props to <Header> component

const headerElement = document.getElementById('header');
react_dom__WEBPACK_IMPORTED_MODULE_1___default.a.render( /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_Header_jsx__WEBPACK_IMPORTED_MODULE_3__["default"], headerElement.dataset), headerElement);
react_dom__WEBPACK_IMPORTED_MODULE_1___default.a.render( /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_Colophon_jsx__WEBPACK_IMPORTED_MODULE_2__["default"]), document.getElementById('colophon'));

const Content = () => {
  const [open, setOpen] = react__WEBPACK_IMPORTED_MODULE_0___default.a.useState(true);
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(react__WEBPACK_IMPORTED_MODULE_0___default.a.Fragment, null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("h1", null, "Hello World!"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("h1", null, "Hello World!"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("h1", null, "Hello World!"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("h1", null, "Hello World!"), open && /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(react__WEBPACK_IMPORTED_MODULE_0___default.a.Fragment, null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("p", null, "Lorem ipsum dolor sit amet ", /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: "#consectetur"
  }, "consectetur adipisicing elit"), ". Eveniet voluptas incidunt atque ipsam, nobis quis inventore, velit libero vel autem tempora, fugit soluta excepturi ", /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: "#foo"
  }, "voluptatum"), "! Soluta possimus nihil dolore hic."), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("p", null, "Lorem ipsum dolor sit amet consectetur adipisicing elit. Aperiam, repellendus ullam cumque sequi deserunt cum possimus, deleniti impedit pariatur atque eligendi. Eius debitis delectus maxime esse a, odio sint mollitia!"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("p", null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: "#bar"
  }, "Lorem ipsum dolor sit amet consectetur"), ", adipisicing elit. Quis tenetur facilis ipsum doloremque magni cum. Praesentium reiciendis vitae omnis ex sint eaque eos necessitatibus assumenda atque reprehenderit, commodi quod. Nam! Lorem ipsum dolor sit amet consectetur adipisicing elit. Obcaecati consectetur similique nulla veritatis a impedit provident eaque dignissimos facere soluta voluptate ab aliquam quidem culpa dolores hic excepturi, eius quae?"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("p", null, "Lorem ipsum dolor sit amet consectetur adipisicing elit. Alias facere magni culpa molestiae voluptates ducimus? Ducimus minus nesciunt tempora ad asperiores! Totam autem dolore eos delectus reprehenderit ipsa animi omnis."), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("p", null, "Lorem ipsum dolor sit, amet consectetur adipisicing elit. ", /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: "#baz"
  }, "Itaque beatae"), " eaque praesentium modi voluptates libero obcaecati earum? Officia impedit distinctio deleniti exercitationem delectus! Assumenda, hic a eaque nobis velit quis."), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("p", null, "Lorem ipsum dolor sit, amet consectetur adipisicing elit. Accusamus magni ut aliquam officiis nostrum consequatur tempore, at repudiandae, laudantium exercitationem itaque cum, et voluptate suscipit modi unde ad doloremque sit! Lorem ipsum dolor sit amet consectetur adipisicing elit. Autem maiores quisquam distinctio quos qui adipisci voluptates perferendis officia commodi, fugit eius est ut corrupti reprehenderit fuga quibusdam, cum itaque sequi?"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("p", null, "Lorem ipsum, dolor sit amet consectetur adipisicing elit. Aperiam deserunt ea natus iusto ipsa, labore in consectetur, beatae commodi voluptas hic, ratione asperiores dicta accusantium optio quas unde omnis error!"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("p", null, "Lorem ipsum dolor sit amet consectetur adipisicing elit. Ipsa maxime quod ex iure eius et, sint doloremque! Libero exercitationem pariatur hic dignissimos, dolorum consequuntur odio consectetur voluptate accusamus voluptatem a."), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("p", null, "Fin.")), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("button", {
    type: "button",
    onClick: () => setOpen(!open)
  }, "Toggle filler text"));
};

react_dom__WEBPACK_IMPORTED_MODULE_1___default.a.render( /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(Content, null), document.getElementById('content')); // const element = (
//     <>
//         <Router>
//             <Page />
//         </Router>
//     </>
// );
// ReactDOM.render(element, document.getElementById('root'));
// Hot Module Replacement

if (false) {}

/***/ }),

/***/ "./assets/javascript/Colophon.jsx":
/*!****************************************!*\
  !*** ./assets/javascript/Colophon.jsx ***!
  \****************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return Colophon; });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _storageAvailable_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./storageAvailable.js */ "./assets/javascript/storageAvailable.js");
/* harmony import */ var _images_colophon_welcome_png__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../images/colophon_welcome.png */ "./assets/images/colophon_welcome.png");



function Colophon() {
  if (!Object(_storageAvailable_js__WEBPACK_IMPORTED_MODULE_1__["default"])('localStorage')) {
    return '';
  } // Prevent saving hidden status:
  // localStorage.clear();


  const [open, setOpen] = react__WEBPACK_IMPORTED_MODULE_0___default.a.useState(true);

  function handleClose(event) {
    event.preventDefault();
    setOpen(false);
    localStorage.setItem('colophon', 'closed');
  }

  if (open && localStorage.getItem('colophon') !== 'closed') {
    return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
      className: "container dark",
      style: {
        position: 'fixed',
        zIndex: 999,
        right: 0,
        bottom: 0,
        left: 0,
        fontSize: '15px',
        color: '#BBB',
        boxShadow: '0 0 10px -5px black'
      }
    }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
      style: {
        padding: '30px 0',
        margin: '0 auto',
        textAlign: 'center'
      }
    }, "Welcome to Videogam.in, a site about videogames.", /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("br", null), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
      href: "/about.php"
    }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("strong", null, "Read more")), " about this site or else ", /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
      href: "#close",
      title: "hide this message and don't show it to me again",
      className: "tooltip",
      onClick: handleClose
    }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("strong", null, "pay me for the door repair charge")), "."), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
      style: {
        position: 'absolute',
        zIndex: 2,
        top: '10px',
        left: '50%',
        width: '192px',
        height: '16px',
        margin: '0 0 0 -96px',
        background: `url(${_images_colophon_welcome_png__WEBPACK_IMPORTED_MODULE_2__["default"]}) no-repeat 0 0`
      }
    }), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
      style: {
        position: 'absolute',
        zIndex: 2,
        bottom: '0',
        left: '50%',
        width: '192px',
        height: '18px',
        margin: '0 0 0 -96px',
        background: `url(${_images_colophon_welcome_png__WEBPACK_IMPORTED_MODULE_2__["default"]}) no-repeat 0 -16px`
      }
    }), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
      style: {
        position: 'absolute',
        zIndex: 1,
        right: '0',
        bottom: '0',
        left: '0',
        width: '100%',
        height: '18px',
        background: `url(${_images_colophon_welcome_png__WEBPACK_IMPORTED_MODULE_2__["default"]}) repeat-x 0 -34px`
      }
    }));
  }

  return '';
}

/***/ }),

/***/ "./assets/javascript/Header.jsx":
/*!**************************************!*\
  !*** ./assets/javascript/Header.jsx ***!
  \**************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return Header; });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _NavMenu_jsx__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./NavMenu.jsx */ "./assets/javascript/NavMenu.jsx");
/* harmony import */ var _Login_jsx__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./Login.jsx */ "./assets/javascript/Login.jsx");
/* harmony import */ var _Search_jsx__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./Search.jsx */ "./assets/javascript/Search.jsx");




function Header(props) {
  const {
    username
  } = props;
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(react__WEBPACK_IMPORTED_MODULE_0___default.a.Fragment, null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_NavMenu_jsx__WEBPACK_IMPORTED_MODULE_1__["default"], null), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_Login_jsx__WEBPACK_IMPORTED_MODULE_2__["default"], {
    username: username
  }), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_Search_jsx__WEBPACK_IMPORTED_MODULE_3__["default"], null));
}

/***/ }),

/***/ "./assets/javascript/Login.jsx":
/*!*************************************!*\
  !*** ./assets/javascript/Login.jsx ***!
  \*************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return Login; });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _Modal_jsx__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Modal.jsx */ "./assets/javascript/Modal.jsx");
/* harmony import */ var _icons_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./icons.js */ "./assets/javascript/icons.js");



function Login(props) {
  const {
    username
  } = props;
  const form = react__WEBPACK_IMPORTED_MODULE_0___default.a.useRef();

  const handleSubmit = event => {
    event.preventDefault();
    console.log(form.current.username.value, form.current.password.value);
  };

  const [open, setOpen] = react__WEBPACK_IMPORTED_MODULE_0___default.a.useState(false);

  const handleOpen = event => {
    event.preventDefault();
    setOpen(true);
  };

  const handleClose = () => {
    setOpen(false);
  };

  const userLink = username && /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("span", {
    className: "user"
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: `~${username}`
  }, username));
  const loginButton = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: "/login.php",
    onClick: handleOpen,
    className: "user user-unknown"
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_icons_js__WEBPACK_IMPORTED_MODULE_2__["QuestionBlock"], {
    className: "user-avatar thumbnail"
  }), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("span", {
    className: "user-username"
  }, "Login"));
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
    id: "login"
  }, userLink || loginButton, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_Modal_jsx__WEBPACK_IMPORTED_MODULE_1__["default"], {
    open: open,
    close: handleClose,
    closeButton: false
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("form", {
    ref: form,
    onSubmit: handleSubmit
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("input", {
    type: "text",
    name: "username",
    placeholder: "Username",
    ref: input => input && input.focus()
  }), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("input", {
    type: "password",
    name: "password",
    placeholder: "Password"
  }), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("button", {
    type: "submit"
  }, "Login"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("button", {
    type: "button",
    onClick: handleClose
  }, "Cancel")))));
}

/***/ }),

/***/ "./assets/javascript/Modal.jsx":
/*!*************************************!*\
  !*** ./assets/javascript/Modal.jsx ***!
  \*************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return Modal; });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var react_transition_group__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! react-transition-group */ "./node_modules/react-transition-group/esm/index.js");
/* harmony import */ var react_icons_bi__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! react-icons/bi */ "./node_modules/react-icons/bi/index.esm.js");



function Modal(props) {
  const {
    children,
    open = true,
    close = null,
    timeout = 500,
    overlay = true,
    closeButton = true
  } = props;

  const CloseButton = () => closeButton && /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("button", {
    type: "button",
    role: "switch",
    "aria-checked": open,
    "aria-label": "Close",
    className: "modal-close close-button",
    onClick: close
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(react_icons_bi__WEBPACK_IMPORTED_MODULE_2__["BiX"], {
    "arial-hidden": "true"
  }));

  const Overlay = () => overlay && /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
    className: "modal-overlay",
    role: "button",
    onClick: close,
    "aria-hidden": "true",
    "aria-label": "close"
  });

  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(react_transition_group__WEBPACK_IMPORTED_MODULE_1__["CSSTransition"], {
    in: open,
    timeout: timeout,
    classNames: "modal",
    unmountOnExit: true
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
    className: "modal modal-container"
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(CloseButton, null), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(Overlay, null), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
    className: "modal-content light"
  }, children)));
}

/***/ }),

/***/ "./assets/javascript/NavMenu.jsx":
/*!***************************************!*\
  !*** ./assets/javascript/NavMenu.jsx ***!
  \***************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return NavMenu; });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var classnames__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! classnames */ "./node_modules/classnames/index.js");
/* harmony import */ var classnames__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(classnames__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var react_transition_group__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! react-transition-group */ "./node_modules/react-transition-group/esm/index.js");
/* harmony import */ var _Modal_jsx__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./Modal.jsx */ "./assets/javascript/Modal.jsx");




function NavMenu(props) {
  const [open, setOpen] = react__WEBPACK_IMPORTED_MODULE_0___default.a.useState(false);

  const toggleOpen = () => {
    setOpen(!open);
  };

  const buttonClasses = classnames__WEBPACK_IMPORTED_MODULE_1___default()({
    'access-button': true,
    active: open,
    inactive: !open
  });
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(react_transition_group__WEBPACK_IMPORTED_MODULE_2__["CSSTransition"], {
    in: open,
    timeout: 1500
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("nav", {
    id: "navmenu"
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("ul", null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("li", {
    className: "navmenu-item-container"
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("h6", null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: "/games"
  }, "Start Game"))), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("li", {
    className: "navmenu-item-container"
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("button", {
    type: "button",
    role: "switch",
    "aria-checked": open,
    id: "menu",
    className: buttonClasses,
    onClick: toggleOpen
  }, "Options")), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("li", {
    className: "navmenu-item-container hidden"
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: "/games"
  }, "Games")), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("li", {
    className: "navmenu-item-container hidden"
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: "/people"
  }, "People")), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("li", {
    className: "navmenu-item-container hidden"
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: "/music"
  }, "Music")))));
}

/***/ }),

/***/ "./assets/javascript/Search.jsx":
/*!**************************************!*\
  !*** ./assets/javascript/Search.jsx ***!
  \**************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return Search; });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var react_icons_bi__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! react-icons/bi */ "./node_modules/react-icons/bi/index.esm.js");
/* harmony import */ var _Modal_jsx__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./Modal.jsx */ "./assets/javascript/Modal.jsx");
/* eslint-disable react/button-has-type */



const API_ENDPOINT = '/api/search?q=';
function Search() {
  const [searchTerm, setSearchTerm] = react__WEBPACK_IMPORTED_MODULE_0___default.a.useState('');
  const resultsInitialState = {
    hits: [],
    isLoading: false,
    isError: false
  };

  const resultsReducer = (state, action) => {
    switch (action.type) {
      case 'SEARCH_FETCH_INIT':
        return { ...state,
          isLoading: true,
          isError: false
        };

      case 'SEARCH_FETCH_SUCCESS':
        return { ...state,
          isLoading: false,
          isError: false,
          hits: action.payload
        };

      case 'SEARCH_FETCH_FAIL':
        return { ...state,
          isLoading: false,
          isError: true
        };

      case 'RESET':
        return { ...state,
          isLoading: false,
          isError: false,
          hits: []
        };

      default:
        throw new Error();
    }
  }; // call `dispatchResults` to change `results` object


  const [results, dispatchResults] = react__WEBPACK_IMPORTED_MODULE_0___default.a.useReducer(resultsReducer, resultsInitialState);

  const handleSearch = event => {
    setSearchTerm(event.target.value);
  };

  react__WEBPACK_IMPORTED_MODULE_0___default.a.useEffect(() => {
    if (!searchTerm) {
      dispatchResults({
        type: 'RESET'
      });
      return;
    }

    if (searchTerm.length < 3) {
      return;
    } // Mark search form as initializing/loading


    dispatchResults({
      type: 'SEARCH_FETCH_INIT'
    }); // Fetch from API

    const url = API_ENDPOINT + searchTerm;
    fetch(url).then(response => response.json()).then(result => {
      console.log('fetch result', result);

      if (!result.collection.items.length) {
        dispatchResults({
          type: 'SEARCH_FETCH_FAIL'
        });
      } else {
        dispatchResults({
          type: 'SEARCH_FETCH_SUCCESS',
          payload: result.collection.items
        });
      }
    }).catch(() => dispatchResults({
      type: 'SEARCH_FETCH_FAIL'
    }));
  }, [searchTerm]);
  const [open, setOpen] = react__WEBPACK_IMPORTED_MODULE_0___default.a.useState(false);

  const handleClose = () => {
    setOpen(false);
  };

  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
    id: "search"
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("button", {
    type: "button",
    className: "access-button",
    onClick: () => setOpen(true)
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(react_icons_bi__WEBPACK_IMPORTED_MODULE_1__["BiSearch"], {
    size: "28",
    title: "Search"
  })), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_Modal_jsx__WEBPACK_IMPORTED_MODULE_2__["default"], {
    open: open,
    close: handleClose
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("input", {
    id: "searchform",
    type: "text",
    value: searchTerm,
    placeholder: "Search all the things",
    onChange: handleSearch,
    ref: input => input && input.focus()
  }), ' ', /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("button", {
    type: "reset",
    onClick: () => setSearchTerm('')
  }, "Reset"), results.isError && /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("p", null, "Something went wrong"), results.isLoading ? /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("p", null, "Loading...") : /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(SearchResults, {
    results: results
  }))));
}

function SearchResults(props) {
  const {
    results
  } = props;
  if (results.hits.length === 0) return null;
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("ul", null, results.hits.map(item => /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(SearchResult, {
    key: item.title_sort,
    item: item
  })));
}
/**
 * Item component
 * @param {Object} props.item Item object
 * @param {} onRemoveItem
 */


function SearchResult(props) {
  const {
    item
  } = props;
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("li", null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: item.links.page
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("dfn", null, item.title), ' ', /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("span", null, "(", item.type, ")")));
} // ReactDOM.render(
//     React.createElement(Search),
//     document.getElementById('search'),
// );

/***/ }),

/***/ "./assets/javascript/icons.js":
/*!************************************!*\
  !*** ./assets/javascript/icons.js ***!
  \************************************/
/*! exports provided: QuestionBlock, default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "QuestionBlock", function() { return QuestionBlock; });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var classnames__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! classnames */ "./node_modules/classnames/index.js");
/* harmony import */ var classnames__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(classnames__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _images_icons_questionblock_png__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../images/icons/questionblock.png */ "./assets/images/icons/questionblock.png");



function QuestionBlock({
  className: classNameProp,
  ...props
}) {
  const className = classnames__WEBPACK_IMPORTED_MODULE_1___default()(classNameProp, 'icon');
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement('img', { ...props,
    src: _images_icons_questionblock_png__WEBPACK_IMPORTED_MODULE_2__["default"],
    alt: '[?]',
    className
  });
}
/* harmony default export */ __webpack_exports__["default"] = ({
  QuestionBlock
});

/***/ }),

/***/ "./assets/javascript/storageAvailable.js":
/*!***********************************************!*\
  !*** ./assets/javascript/storageAvailable.js ***!
  \***********************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return storageAvailable; });
// Usage:
// if (storageAvailable('localStorage')) {/** */}
function storageAvailable(type) {
  let storage;

  try {
    storage = window[type];
    const x = '__storage_test__';
    storage.setItem(x, x);
    storage.removeItem(x);
    return true;
  } catch (e) {
    return e instanceof DOMException && ( // everything except Firefox
    e.code === 22 // Firefox
    || e.code === 1014 // test name field too, because code might not be present
    // everything except Firefox
    || e.name === 'QuotaExceededError' // Firefox
    || e.name === 'NS_ERROR_DOM_QUOTA_REACHED') // acknowledge QuotaExceededError only if there's something already stored
    && storage && storage.length !== 0;
  }
}

/***/ }),

/***/ "./assets/styles/app.scss":
/*!********************************!*\
  !*** ./assets/styles/app.scss ***!
  \********************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var api = __webpack_require__(/*! ../../node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js */ "./node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js");
            var content = __webpack_require__(/*! !../../node_modules/css-loader/dist/cjs.js!../../node_modules/resolve-url-loader!../../node_modules/sass-loader/dist/cjs.js!./app.scss */ "./node_modules/css-loader/dist/cjs.js!./node_modules/resolve-url-loader/index.js!./node_modules/sass-loader/dist/cjs.js!./assets/styles/app.scss");

            content = content.__esModule ? content.default : content;

            if (typeof content === 'string') {
              content = [[module.i, content, '']];
            }

var options = {};

options.insert = "head";
options.singleton = false;

var update = api(content, options);



module.exports = content.locals || {};

/***/ }),

/***/ "./node_modules/css-loader/dist/cjs.js!./node_modules/resolve-url-loader/index.js!./node_modules/sass-loader/dist/cjs.js!./assets/styles/app.scss":
/*!***********************************************************************************************************************************************!*\
  !*** ./node_modules/css-loader/dist/cjs.js!./node_modules/resolve-url-loader!./node_modules/sass-loader/dist/cjs.js!./assets/styles/app.scss ***!
  \***********************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../node_modules/css-loader/dist/runtime/api.js */ "./node_modules/css-loader/dist/runtime/api.js");
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _node_modules_css_loader_dist_runtime_getUrl_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../node_modules/css-loader/dist/runtime/getUrl.js */ "./node_modules/css-loader/dist/runtime/getUrl.js");
/* harmony import */ var _node_modules_css_loader_dist_runtime_getUrl_js__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_node_modules_css_loader_dist_runtime_getUrl_js__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _fonts_PixelEmulator_woff__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../fonts/PixelEmulator.woff */ "./assets/fonts/PixelEmulator.woff");
/* harmony import */ var _fonts_Emulogic_woff2__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../fonts/Emulogic.woff2 */ "./assets/fonts/Emulogic.woff2");
/* harmony import */ var _fonts_prstart_woff__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../fonts/prstart.woff */ "./assets/fonts/prstart.woff");
/* harmony import */ var _fonts_prstartk_woff__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ../fonts/prstartk.woff */ "./assets/fonts/prstartk.woff");
/* harmony import */ var _fonts_YosterIslandRegular_woff2__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ../fonts/YosterIslandRegular.woff2 */ "./assets/fonts/YosterIslandRegular.woff2");
/* harmony import */ var _images_twitter_sm_png__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ../images/twitter_sm.png */ "./assets/images/twitter_sm.png");
/* harmony import */ var _images_footer_diorama_png__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ../images/footer_diorama.png */ "./assets/images/footer_diorama.png");
// Imports









var ___CSS_LOADER_EXPORT___ = _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default()(true);
var ___CSS_LOADER_URL_REPLACEMENT_0___ = _node_modules_css_loader_dist_runtime_getUrl_js__WEBPACK_IMPORTED_MODULE_1___default()(_fonts_PixelEmulator_woff__WEBPACK_IMPORTED_MODULE_2__["default"]);
var ___CSS_LOADER_URL_REPLACEMENT_1___ = _node_modules_css_loader_dist_runtime_getUrl_js__WEBPACK_IMPORTED_MODULE_1___default()(_fonts_Emulogic_woff2__WEBPACK_IMPORTED_MODULE_3__["default"]);
var ___CSS_LOADER_URL_REPLACEMENT_2___ = _node_modules_css_loader_dist_runtime_getUrl_js__WEBPACK_IMPORTED_MODULE_1___default()(_fonts_prstart_woff__WEBPACK_IMPORTED_MODULE_4__["default"]);
var ___CSS_LOADER_URL_REPLACEMENT_3___ = _node_modules_css_loader_dist_runtime_getUrl_js__WEBPACK_IMPORTED_MODULE_1___default()(_fonts_prstartk_woff__WEBPACK_IMPORTED_MODULE_5__["default"]);
var ___CSS_LOADER_URL_REPLACEMENT_4___ = _node_modules_css_loader_dist_runtime_getUrl_js__WEBPACK_IMPORTED_MODULE_1___default()(_fonts_YosterIslandRegular_woff2__WEBPACK_IMPORTED_MODULE_6__["default"]);
var ___CSS_LOADER_URL_REPLACEMENT_5___ = _node_modules_css_loader_dist_runtime_getUrl_js__WEBPACK_IMPORTED_MODULE_1___default()(_images_twitter_sm_png__WEBPACK_IMPORTED_MODULE_7__["default"]);
var ___CSS_LOADER_URL_REPLACEMENT_6___ = _node_modules_css_loader_dist_runtime_getUrl_js__WEBPACK_IMPORTED_MODULE_1___default()(_images_footer_diorama_png__WEBPACK_IMPORTED_MODULE_8__["default"]);
// Module
___CSS_LOADER_EXPORT___.push([module.i, "@font-face {\n  font-family: \"Pixel Emulator\";\n  font-style: normal;\n  font-weight: normal;\n  src: url(" + ___CSS_LOADER_URL_REPLACEMENT_0___ + ") format(\"woff\");\n}\n@font-face {\n  font-family: \"Emulogic\";\n  font-style: normal;\n  font-weight: normal;\n  src: url(" + ___CSS_LOADER_URL_REPLACEMENT_1___ + ") format(\"woff2\");\n}\n@font-face {\n  font-family: \"Press Start\";\n  font-style: normal;\n  font-weight: normal;\n  src: url(" + ___CSS_LOADER_URL_REPLACEMENT_2___ + ") format(\"woff\"), url(" + ___CSS_LOADER_URL_REPLACEMENT_3___ + ") format(\"woff\");\n}\n@font-face {\n  font-family: \"Yoster Island\";\n  font-style: normal;\n  font-weight: normal;\n  src: url(" + ___CSS_LOADER_URL_REPLACEMENT_4___ + ") format(\"woff2\");\n}\n:root {\n  font: normal 100% sans-serif;\n  font-size: calc(100vw / 25);\n  color: white;\n}\n@media (min-width: 641px) {\n  :root {\n    font-size: calc(100vw / 70);\n  }\n}\n\na,\n.a {\n  color: #3399ff;\n  text-decoration: underline;\n  cursor: pointer;\n}\na:active,\n.a:active {\n  color: #6b3ea8;\n}\na:hover,\n.a:hover {\n  color: #66b3ff;\n  border-color: #66b3ff;\n}\n\nfieldset {\n  margin-left: 0;\n  margin-right: 0;\n  padding: 5px 10px 10px 10px;\n  border: 1px solid #ccc;\n}\n\nlegend {\n  color: #666;\n}\n\n.inputitem, button:not(.access-button):not(.close-button),\ninput[type=button],\ninput[type=submit],\ninput[type=reset],\n.faux-button, input[type=text],\ninput[type=password],\ntextarea,\nselect,\n.inputfield {\n  border-radius: 0.1em;\n  margin: 0;\n  padding: 0.3em 0.5em;\n}\n\ninput[type=text],\ninput[type=password],\ntextarea,\nselect,\n.inputfield {\n  border-width: 1px;\n  border-style: solid;\n  border-color: #666 #bbb #bbb #666;\n  background-color: white;\n  outline: none;\n  background: white;\n  background: linear-gradient(#e0e0e0, white 3px);\n}\n\ntextarea {\n  font-family: monospace;\n}\n\nselect {\n  padding: 2px;\n}\n\noptgroup {\n  padding-top: 2px;\n  font-weight: normal;\n  font-style: italic;\n  color: #777;\n  background-color: #eee;\n}\n\noptgroup > option {\n  padding-left: 20px;\n  background-color: #fff;\n  color: black;\n}\n\noptgroup > option:first-child {\n  margin-top: 2px;\n}\n\nbutton:not(.access-button):not(.close-button),\ninput[type=button],\ninput[type=submit],\ninput[type=reset],\n.faux-button {\n  color: #444;\n  text-shadow: 0 -1px #dadada, 0 1px #eee;\n  background: #ddd;\n  background: -moz-linear-gradient(top, #eee 50%, #ddd 50%);\n  background: -webkit-gradient(linear, left top, left bottom, color-stop(50%, #eee), color-stop(50%, #ddd));\n  border-width: 1px;\n  border-style: solid;\n  border-color: #ddd #aaa #aaa #ddd;\n  box-shadow: 0 1px 0 rgba(0, 0, 0, 0.1);\n  cursor: pointer;\n}\nbutton:not(.access-button):not(.close-button):hover,\ninput[type=button]:hover,\ninput[type=submit]:hover,\ninput[type=reset]:hover,\n.faux-button:hover {\n  border-color: #777;\n}\nbutton:not(.access-button):not(.close-button):active, button:not(.access-button):not(.close-button).active,\ninput[type=button]:active,\ninput[type=button].active,\ninput[type=submit]:active,\ninput[type=submit].active,\ninput[type=reset]:active,\ninput[type=reset].active,\n.faux-button:active,\n.faux-button.active {\n  box-shadow: none;\n  background: #ddd;\n  border-color: #aaa #ccc #ccc #aaa;\n}\nbutton:not(.access-button):not(.close-button)[disabled=disabled],\ninput[type=button][disabled=disabled],\ninput[type=submit][disabled=disabled],\ninput[type=reset][disabled=disabled],\n.faux-button[disabled=disabled] {\n  color: #bbb;\n  cursor: not-allowed;\n}\nbutton:not(.access-button):not(.close-button).submit:hover,\ninput[type=button].submit:hover,\ninput[type=submit].submit:hover,\ninput[type=reset].submit:hover,\n.faux-button.submit:hover {\n  background: #00a264;\n  border-color: #016c43;\n  color: white;\n  text-shadow: none;\n}\nbutton:not(.access-button):not(.close-button).cancel:hover,\ninput[type=button].cancel:hover,\ninput[type=submit].cancel:hover,\ninput[type=reset].cancel:hover,\n.faux-button.cancel:hover {\n  background: #dd3333;\n  border-color: #a81c1c;\n  color: white;\n  text-shadow: none;\n}\n\nimg {\n  vertical-align: middle;\n}\n\nh1 {\n  font-family: \"Pixel Emulator\";\n  font-weight: 300;\n  line-height: 1.1;\n  margin-top: 0;\n  margin-bottom: 0.75em;\n  color: whitesmoke;\n  text-shadow: 1px 1px 0 rgba(0, 0, 0, 0.2);\n}\nh1 + h1 {\n  font-family: \"Emulogic\";\n}\nh1 + h1 + h1 {\n  font-family: \"Press Start\";\n  text-transform: uppercase;\n}\nh1 + h1 + h1 + h1 {\n  font-family: \"Yoster Island\";\n}\n\nh2 {\n  margin-bottom: 0.5em;\n  font-weight: normal;\n  color: whitesmoke;\n}\n\nh3 {\n  margin: 1em 0 0.5em;\n}\n\nh4 {\n  margin: 1em 0 0.5em;\n}\n\nh5 {\n  margin: 1em 0 0.5em;\n  font-size: 1.25rem;\n}\n\nh6 {\n  margin: 1em 0 0.5em;\n}\n\n.grid-page, body > footer, body {\n  display: grid;\n  grid-template-columns: [margin-start] 1rem [narrow-gutter-start] 2.5rem [wide-gutter-start] auto [wide-gutter-end] 2.5rem [narrow-gutter-end] 1rem [margin-end];\n}\n.grid-page > *, body > footer > *, body > * {\n  grid-column: narrow-gutter-start/narrow-gutter-end;\n}\n@media (min-width: 641px) {\n  .grid-page > *, body > footer > *, body > * {\n    grid-column: wide-gutter-start/wide-gutter-end;\n  }\n}\n\n.fullwidth, body > footer, body > header {\n  grid-column: margin-start/margin-end;\n}\n\nbody {\n  min-height: 100vh;\n  box-sizing: border-box;\n  padding: 0;\n  background-color: #18191a;\n  text-align: left;\n  grid-template-rows: min-content auto max-content;\n}\nbody > header,\nbody > main,\nbody > footer {\n  padding-top: 1rem;\n  padding-bottom: 1rem;\n}\n@media (min-width: 641px) {\n  body > header,\nbody > main,\nbody > footer {\n    padding-top: 3.5rem;\n  }\n}\n@media (min-width: 641px) {\n  body > header,\nbody > main,\nbody > footer {\n    padding-bottom: 3.5rem;\n  }\n}\n\nbody > header {\n  padding-left: 1rem;\n  padding-right: 1rem;\n  background-color: #18191a;\n  border-bottom: 1px solid #242526;\n  display: flex;\n  flex-direction: row;\n  justify-content: space-between;\n  align-items: flex-start;\n}\n@media (min-width: 641px) {\n  body > header {\n    padding-left: 3.5rem;\n  }\n}\n@media (min-width: 641px) {\n  body > header {\n    padding-right: 3.5rem;\n  }\n}\nbody > header > *:first-child {\n  flex: max-content;\n}\nbody > header > *:first-child ~ * {\n  margin-left: 1em;\n}\nbody > header a,\nbody > header button.access-button,\nbody > header button.close-button {\n  color: #e0e0e0;\n  text-decoration: none;\n}\nbody > header a:hover, body > header a:hover svg,\nbody > header button.access-button:hover,\nbody > header button.close-button:hover,\nbody > header button.access-button:hover svg,\nbody > header button.close-button:hover svg {\n  color: white !important;\n}\n\n#navmenu .hidden, #navmenu.exit-done .hidden {\n  max-height: 0;\n  opacity: 0;\n  overflow: hidden;\n  transition: all 500ms;\n}\n#navmenu.enter .hidden {\n  max-height: 100px;\n  opacity: 1;\n}\n#navmenu.enter-active .hidden, #navmenu.enter-done .hidden {\n  max-height: 55px;\n  opacity: 1;\n}\n#navmenu.exit .hidden {\n  max-height: 0;\n  opacity: 0;\n}\n#navmenu ul, #navmenu li {\n  list-style-type: none;\n  margin: 0;\n  padding: 0;\n}\n#navmenu .navmenu-item-container {\n  margin: 0;\n  padding: 0;\n}\n#navmenu .navmenu-item-container > * {\n  display: block;\n  margin: 0.5em 0 0;\n  padding: 0;\n  font-family: \"Pixel Emulator\";\n  font-size: 1.5em;\n  text-decoration: none;\n}\n\n@media (max-width: 640px) {\n  #login .user-unknown .user-username {\n    display: none;\n  }\n}\n#login .modal-content {\n  width: 290px;\n  margin-right: auto;\n  margin-left: auto;\n  background-color: transparent !important;\n}\n#login form {\n  margin-top: -1em;\n  display: flex;\n  flex-direction: column;\n  align-items: flex-start;\n}\n#login form > * {\n  margin-top: 1em;\n}\n#login form input {\n  color: #666;\n  border-width: 0;\n  border-radius: 0.2em;\n}\n#login form button {\n  font-weight: bold;\n  border-width: 0;\n  box-shadow: 1px 1px 3px 1px black;\n  -moz-box-shadow: 1px 1px 3px 1px black;\n  -webkit-box-shadow: 1px 1px 3px 1px black;\n}\n#login form button:active {\n  margin: 1px 0 0 1px;\n  box-shadow: 0 0 3px 1px black;\n  -moz-box-shadow: 0 0 3px 1px black;\n  -webkit-box-shadow: 0 0 3px 1px black;\n}\n#login form button + button {\n  margin-left: 1em;\n}\n\nbody > #content {\n  padding-top: 1rem;\n  padding-bottom: 1rem;\n}\n@media (min-width: 641px) {\n  body > #content {\n    padding-top: 3.5rem;\n  }\n}\n@media (min-width: 641px) {\n  body > #content {\n    padding-bottom: 3.5rem;\n  }\n}\n\nbody > footer {\n  position: relative;\n  z-index: 1;\n  padding-top: 1rem;\n  padding-bottom: 1rem;\n  border-top: 1px solid #242526;\n  color: #999;\n}\n@media (min-width: 641px) {\n  body > footer {\n    padding-top: 3.5rem;\n  }\n}\n@media (min-width: 641px) {\n  body > footer {\n    padding-bottom: 3.5rem;\n  }\n}\nbody > footer a {\n  color: #aaa;\n}\nbody > footer a:hover {\n  color: #ccc;\n}\nbody > footer h5 {\n  margin: 0 0 5px;\n  font-size: 15px;\n}\nbody > footer ul {\n  margin: 0;\n  padding: 0;\n  list-style: none;\n}\nbody > footer ul li {\n  margin: 0;\n  padding: 0;\n}\nbody > footer .about,\nbody > footer .featured {\n  display: none;\n}\nbody > footer .about li a,\nbody > footer .featured li a {\n  display: block;\n  margin: 0 14px 7px 0;\n  font-size: 14px;\n  background-position: left center;\n  background-repeat: no-repeat;\n}\nbody > footer .about li a .link-twitter,\nbody > footer .featured li a .link-twitter {\n  padding-left: 20px;\n  background: url(" + ___CSS_LOADER_URL_REPLACEMENT_5___ + ") no-repeat left center;\n}\nbody > footer #diorama {\n  width: 100%;\n  height: 20px;\n}\nbody > footer #diorama div {\n  position: absolute;\n  background-image: url(" + ___CSS_LOADER_URL_REPLACEMENT_6___ + ");\n}\n\n.dark, #login .modal-content {\n  background-color: black;\n  color: white;\n}\n.dark a, #login .modal-content a {\n  color: white;\n}\n.dark a:hover, #login .modal-content a:hover {\n  color: lightgray;\n}\n\n.light {\n  background-color: white;\n  color: black;\n}\n.light a {\n  color: black;\n}\n.light a:hover {\n  color: #2e2e2e;\n}\n\n.red, .close-button {\n  color: #dd3333;\n}\n.red:hover, .close-button:hover {\n  color: #f17878;\n}\n\n.access-button, .close-button {\n  border: 0;\n  padding: 0;\n  margin: 0;\n  background-color: transparent;\n  background-image: none;\n  cursor: pointer;\n}\n\n.close-button {\n  font-size: 2em;\n}\n\n.modal {\n  position: fixed;\n  z-index: 10;\n  top: 0;\n  right: 0;\n  bottom: 0;\n  left: 0;\n  display: grid;\n}\n\n.modal-container.modal-enter {\n  opacity: 0;\n}\n.modal-container.modal-enter-active {\n  opacity: 1;\n  transition: all 500ms;\n}\n.modal-container.modal-exit {\n  opacity: 1;\n}\n.modal-container.modal-exit-active {\n  opacity: 0;\n  transition: opacity 500ms;\n}\n\n.modal-overlay {\n  width: 100%;\n  height: 100%;\n  z-index: 10;\n  /* places the modal overlay between the main page and the modal content*/\n  background-color: rgba(0, 0, 0, 0.95);\n  position: fixed;\n  top: 0;\n  left: 0;\n  margin: 0;\n  padding: 0;\n  transition: all 0.3s;\n}\n\n.modal-content {\n  width: auto;\n  box-sizing: border-box;\n  margin: 0.5rem;\n  padding: 2rem;\n  position: relative;\n  z-index: 11;\n  /* places the modal dialog on top of overlay */\n  top: 0;\n  left: 0;\n  display: flex;\n  flex-direction: column;\n  background-color: white;\n}\n.modal-content h5 {\n  margin-top: 0;\n}\n@media (min-width: 640px) {\n  .modal-content {\n    margin: auto;\n    max-width: 500px;\n  }\n}\n\n.modal-close {\n  position: absolute;\n  z-index: 12;\n  top: 0.5rem;\n  right: 0.5rem;\n}\n\n.user {\n  height: 20px;\n  line-height: 20px;\n}\n.user .user-username {\n  display: inline-block;\n  vertical-align: middle;\n}\n.user .user-avatar.thumbnail {\n  display: inline-block;\n  vertical-align: middle;\n  margin-right: 5px;\n}\n\n.user-avatar.big img {\n  width: 144px;\n  height: 144px;\n}\n.user-avatar.icon img {\n  width: 48px;\n  height: 48px;\n}\n.user-avatar.thumbnail img {\n  width: 20px;\n  height: 20px;\n}", "",{"version":3,"sources":["webpack://app.scss"],"names":[],"mappings":"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","sourcesContent":["$blue: #3399ff;\r\n$lightblue: #66b3ff;\r\n$darkblue: #336699;\r\n$red: #dd3333;\r\n$green: #00a264;\r\n$purple: #6b3ea8;\r\n$color-body: rgb(24, 25, 26);\r\n$color-surface: rgb(36, 37, 38);\r\n$color-comment: rgb(58, 59, 60);\r\n\r\n$img-dir: \"../images\";\r\n\r\n// Breakpoint settings and methods\r\n\r\n$breakpoint-mobile: 640px;\r\n\r\n@mixin breakpoint($view) {\r\n    @if $view == mobile {\r\n        @media (max-width: $breakpoint-mobile) {\r\n            @content;\r\n        }\r\n    } @else {\r\n        @media (min-width: $breakpoint-mobile + 1px) {\r\n            @content;\r\n        }\r\n    }\r\n}\r\n\r\n// Spacing settings and methods\r\n\r\n$spacing-mobile: 1rem;\r\n$spacing-monitor: 3.5rem;\r\n\r\n@mixin spacing($prop) {\r\n    #{$prop}: $spacing-mobile;\r\n    @include breakpoint(screen) {\r\n        #{$prop}: $spacing-monitor;\r\n    }\r\n}\r\n\r\n// Fonts\r\n\r\n@font-face {\r\n    font-family: \"Pixel Emulator\";\r\n    font-style: normal;\r\n    font-weight: normal;\r\n    src: url(\"../fonts/PixelEmulator.woff\") format(\"woff\");\r\n}\r\n\r\n@font-face {\r\n    font-family: \"Emulogic\";\r\n    font-style: normal;\r\n    font-weight: normal;\r\n    src: url(\"../fonts/Emulogic.woff2\") format(\"woff2\");\r\n}\r\n\r\n@font-face {\r\n    font-family: \"Press Start\";\r\n    font-style: normal;\r\n    font-weight: normal;\r\n    src: url(\"../fonts/prstart.woff\") format(\"woff\"), url(\"../fonts/prstartk.woff\") format(\"woff\");\r\n}\r\n\r\n@font-face {\r\n    font-family: \"Yoster Island\";\r\n    font-style: normal;\r\n    font-weight: normal;\r\n    src: url(\"../fonts/YosterIslandRegular.woff2\") format(\"woff2\");\r\n}\r\n\r\n// Top Level (Global)\r\n\r\n:root {\r\n    font: normal 100% sans-serif;\r\n    font-size: calc(100vw / 25);\r\n    color: white;\r\n    @include breakpoint(screen) {\r\n        font-size: calc(100vw / 70);\r\n    }\r\n}\r\n\r\na,\r\n.a {\r\n    color: $blue;\r\n    text-decoration: underline;\r\n    cursor: pointer;\r\n    &:active {\r\n        color: $purple;\r\n    }\r\n    &:hover {\r\n        color: $lightblue;\r\n        border-color: $lightblue;\r\n    }\r\n}\r\n\r\nfieldset {\r\n    margin-left: 0;\r\n    margin-right: 0;\r\n    padding: 5px 10px 10px 10px;\r\n    border: 1px solid #ccc;\r\n}\r\nlegend {\r\n    color: #666;\r\n}\r\n.inputitem {\r\n    border-radius: 0.1em;\r\n    margin: 0;\r\n    padding: 0.3em 0.5em;\r\n}\r\ninput[type=\"text\"],\r\ninput[type=\"password\"],\r\ntextarea,\r\nselect,\r\n.inputfield {\r\n    border-width: 1px;\r\n    border-style: solid;\r\n    border-color: #666 #bbb #bbb #666;\r\n    background-color: white;\r\n    outline: none;\r\n    background: white;\r\n    background: linear-gradient(#e0e0e0, white 3px);\r\n    @extend .inputitem;\r\n}\r\ntextarea {\r\n    font-family: monospace;\r\n}\r\nselect {\r\n    padding: 2px;\r\n}\r\noptgroup {\r\n    padding-top: 2px;\r\n    font-weight: normal;\r\n    font-style: italic;\r\n    color: #777;\r\n    background-color: #eee;\r\n}\r\noptgroup > option {\r\n    padding-left: 20px;\r\n    background-color: #fff;\r\n    color: black;\r\n}\r\noptgroup > option:first-child {\r\n    margin-top: 2px;\r\n}\r\n\r\n// .button-blue {\r\n// \t@link-color: darken(@lightblue, 16%);\r\n// \tcolor:rgba(255,255,255,.93); text-shadow:0 -1px @link-color, 0 1px @lightblue;\r\n// \tbackground:@link-color; background:-moz-linear-gradient(top, @lightblue 50%, @link-color 50%); background:-webkit-gradient(linear, left top, left bottom, color-stop(50%,@lightblue), color-stop(50%,@link-color));\r\n// \tborder-color:darken(@lightblue, 10%) darken(@link-color, 5%) darken(@link-color, 5%) darken(@lightblue, 5%);\r\n// \tbox-shadow:0 1px 1px rgba(0,0,0,.2);\r\n// \t&:hover { border-color:darken(@link-color, 25%); }\r\n// \t&:active { box-shadow:none; background:darken(@lightblue, 5%); border-color:darken(@link-color, 12%) darken(@lightblue, 12%) darken(@lightblue, 12%) darken(@link-color, 12%); }\r\n// }\r\nbutton:not(.access-button),\r\ninput[type=\"button\"],\r\ninput[type=\"submit\"],\r\ninput[type=\"reset\"],\r\n.faux-button {\r\n    color: #444;\r\n    text-shadow: 0 -1px #dadada, 0 1px #eee;\r\n    background: #ddd;\r\n    background: -moz-linear-gradient(top, #eee 50%, #ddd 50%);\r\n    background: -webkit-gradient(linear, left top, left bottom, color-stop(50%, #eee), color-stop(50%, #ddd));\r\n    border-width: 1px;\r\n    border-style: solid;\r\n    border-color: #ddd #aaa #aaa #ddd;\r\n    box-shadow: 0 1px 0 rgba(0, 0, 0, 0.1);\r\n    cursor: pointer;\r\n    @extend .inputitem;\r\n    &:hover {\r\n        border-color: #777;\r\n    }\r\n    &:active,\r\n    &.active {\r\n        box-shadow: none;\r\n        background: #ddd;\r\n        border-color: #aaa #ccc #ccc #aaa;\r\n    }\r\n    &[disabled=\"disabled\"] {\r\n        color: #bbb;\r\n        cursor: not-allowed;\r\n    }\r\n    &.submit:hover {\r\n        background: $green;\r\n        border-color: #016c43;\r\n        color: white;\r\n        text-shadow: none;\r\n    }\r\n    &.cancel:hover {\r\n        background: $red;\r\n        border-color: darken($red, 15%);\r\n        color: white;\r\n        text-shadow: none;\r\n    }\r\n}\r\n\r\nimg {\r\n    vertical-align: middle;\r\n}\r\n\r\nh1 {\r\n    font-family: \"Pixel Emulator\";\r\n    font-weight: 300;\r\n    line-height: 1.1;\r\n    margin-top: 0;\r\n    margin-bottom: 0.75em;\r\n    color: whitesmoke;\r\n    text-shadow: 1px 1px 0 rgba(0, 0, 0, 0.2);\r\n    + h1 {\r\n        font-family: \"Emulogic\";\r\n        + h1 {\r\n            font-family: \"Press Start\";\r\n            text-transform: uppercase;\r\n            + h1 { font-family: \"Yoster Island\"; }\r\n        }\r\n    }\r\n}\r\nh2 {\r\n    margin-bottom: 0.5em;\r\n    font-weight: normal;\r\n    color: whitesmoke;\r\n}\r\nh3 {\r\n    margin: 1em 0 0.5em;\r\n}\r\nh4 {\r\n    margin: 1em 0 0.5em;\r\n}\r\nh5 {\r\n    margin: 1em 0 0.5em;\r\n    font-size: 1.25rem;\r\n}\r\nh6 {\r\n    margin: 1em 0 0.5em;\r\n}\r\n\r\n// Page Layouts\r\n\r\n// A template for fullwidth elements;\r\n// Top-level <body> and other full-width elements.\r\n.grid-page {\r\n    display: grid;\r\n    grid-template-columns: [margin-start] $spacing-mobile [narrow-gutter-start] $spacing-monitor - $spacing-mobile [wide-gutter-start] auto [wide-gutter-end] $spacing-monitor - $spacing-mobile [narrow-gutter-end] $spacing-mobile [margin-end];\r\n    & > * {\r\n        grid-column: narrow-gutter-start / narrow-gutter-end;\r\n        @include breakpoint(screen) {\r\n            grid-column: wide-gutter-start / wide-gutter-end;\r\n        }\r\n    }\r\n}\r\n.fullwidth {\r\n    grid-column: margin-start / margin-end;\r\n}\r\n\r\nbody {\r\n    min-height: 100vh;\r\n    box-sizing: border-box;\r\n    padding: 0;\r\n    background: {\r\n        color: $color-body;\r\n    }\r\n    text-align: left;\r\n    @extend .grid-page;\r\n    grid-template-rows: min-content auto max-content;\r\n    > header,\r\n    > main,\r\n    > footer {\r\n        @include spacing(padding-top);\r\n        @include spacing(padding-bottom);\r\n    }\r\n}\r\n\r\nbody > header {\r\n    @extend .fullwidth;\r\n    @include spacing(padding-left);\r\n    @include spacing(padding-right);\r\n    background-color: $color-body;\r\n    border-bottom: 1px solid $color-surface;\r\n    display: flex;\r\n    flex-direction: row;\r\n    justify-content: space-between;\r\n    align-items: flex-start;\r\n    & > *:first-child {\r\n        flex: max-content;\r\n        & ~ * {\r\n            margin-left: 1em;\r\n        }\r\n    }\r\n    a,\r\n    button.access-button {\r\n        color: rgb(224, 224, 224);\r\n        text-decoration: none;\r\n        &:hover,\r\n        &:hover svg {\r\n            color: white !important;\r\n        }\r\n    }\r\n}\r\n#navmenu {\r\n    & .hidden, &.exit-done .hidden {\r\n        max-height: 0;\r\n        opacity: 0;\r\n        overflow: hidden;\r\n        transition: all 500ms;\r\n    }\r\n    &.enter .hidden {\r\n        max-height: 100px;\r\n        opacity: 1;\r\n    }\r\n    &.enter-active .hidden, &.enter-done .hidden {\r\n        max-height: 55px;\r\n        opacity: 1;\r\n    }\r\n    &.exit .hidden {\r\n        max-height: 0;\r\n        opacity: 0;\r\n    }\r\n    ul, li {\r\n        list-style-type: none;\r\n        margin: 0;\r\n        padding: 0;\r\n    }\r\n    .navmenu-item-container {\r\n        margin: 0;\r\n        padding: 0;\r\n        & > * {\r\n            display: block;\r\n            margin: .5em 0 0;\r\n            padding: 0;\r\n            font-family: \"Pixel Emulator\";\r\n            font-size: 1.5em;\r\n            text-decoration: none;\r\n        }\r\n\r\n    }\r\n}\r\n#login {\r\n    @include breakpoint(mobile) {\r\n        .user-unknown {\r\n            .user-username {\r\n                display: none;\r\n            }\r\n        }\r\n    }\r\n    .modal-content {\r\n        width: 290px;\r\n        margin-right: auto;\r\n        margin-left: auto;\r\n        @extend .dark;\r\n        background-color: transparent !important;\r\n    }\r\n    form {\r\n        margin-top: -1em;\r\n        display: flex;\r\n        flex-direction: column;\r\n        align-items: flex-start;\r\n        & > * {\r\n            margin-top: 1em;\r\n        }\r\n        input {\r\n            color: #666;\r\n            border-width: 0;\r\n            border-radius: 0.2em;\r\n        }\r\n        button {\r\n            font-weight: bold;\r\n            border-width: 0;\r\n            box-shadow: 1px 1px 3px 1px black;\r\n            -moz-box-shadow: 1px 1px 3px 1px black;\r\n            -webkit-box-shadow: 1px 1px 3px 1px black;\r\n            &:active {\r\n                margin: 1px 0 0 1px;\r\n                box-shadow: 0 0 3px 1px black;\r\n                -moz-box-shadow: 0 0 3px 1px black;\r\n                -webkit-box-shadow: 0 0 3px 1px black;\r\n            }\r\n            & + button {\r\n                margin-left: 1em;\r\n            }\r\n        }\r\n    }\r\n}\r\n#search {\r\n}\r\n\r\nbody > #content {\r\n    @include spacing(padding-top);\r\n    @include spacing(padding-bottom);\r\n}\r\n\r\nbody > footer {\r\n    @extend .fullwidth;\r\n    @extend .grid-page;\r\n    position: relative;\r\n    z-index: 1;\r\n    @include spacing(padding-top);\r\n    @include spacing(padding-bottom);\r\n    border-top: 1px solid $color-surface;\r\n    color: #999;\r\n    a {\r\n        color: #aaa;\r\n    }\r\n    a:hover {\r\n        color: #ccc;\r\n    }\r\n    h5 {\r\n        margin: 0 0 5px;\r\n        font-size: 15px;\r\n    }\r\n    ul {\r\n        margin: 0;\r\n        padding: 0;\r\n        list-style: none;\r\n        li {\r\n            margin: 0;\r\n            padding: 0;\r\n        }\r\n    }\r\n    .about,\r\n    .featured {\r\n        display: none;\r\n        li {\r\n            a {\r\n                display: block;\r\n                margin: 0 14px 7px 0;\r\n                font-size: 14px;\r\n                background-position: left center;\r\n                background-repeat: no-repeat;\r\n                .link-twitter {\r\n                    padding-left: 20px;\r\n                    background: url(\"#{$img-dir}/twitter_sm.png\") no-repeat left center;\r\n                }\r\n            }\r\n        }\r\n    }\r\n    .featured ul li {\r\n    }\r\n    #diorama {\r\n        width: 100%;\r\n        height: 20px; // Functions as padding\r\n        div {\r\n            position: absolute;\r\n            background-image: url(\"#{$img-dir}/footer_diorama.png\");\r\n        }\r\n    }\r\n}\r\n\r\n// Custom Classes\r\n\r\n.dark {\r\n    background-color: black;\r\n    color: white;\r\n    a {\r\n        color: white;\r\n        &:hover {\r\n            color: lightgray;\r\n        }\r\n    }\r\n}\r\n.light {\r\n    background-color: white;\r\n    color: black;\r\n    a {\r\n        color: black;\r\n        &:hover {\r\n            color: rgb(46, 46, 46);\r\n        }\r\n    }\r\n}\r\n.red {\r\n    color: $red;\r\n    &:hover {\r\n        color: #f17878;\r\n    }\r\n}\r\n\r\n.access-button {\r\n    border: 0;\r\n    padding: 0;\r\n    margin: 0;\r\n    background-color: transparent;\r\n    background-image: none;\r\n    cursor: pointer;\r\n}\r\n.close-button {\r\n    @extend .access-button;\r\n    @extend .red;\r\n    font-size: 2em;\r\n}\r\n\r\n.modal {\r\n    position: fixed;\r\n    z-index: 10;\r\n    top: 0;\r\n    right: 0;\r\n    bottom: 0;\r\n    left: 0;\r\n    display: grid;\r\n}\r\n.modal-container {\r\n    &.modal-enter {\r\n        opacity: 0;\r\n    }\r\n    &.modal-enter-active {\r\n        opacity: 1;\r\n        transition: all 500ms;\r\n    }\r\n    &.modal-exit {\r\n        opacity: 1;\r\n    }\r\n    &.modal-exit-active {\r\n        opacity: 0;\r\n        transition: opacity 500ms;\r\n    }\r\n}\r\n.modal-overlay {\r\n    width: 100%;\r\n    height: 100%;\r\n    z-index: 10; /* places the modal overlay between the main page and the modal content*/\r\n    background-color: rgba(0, 0, 0, 0.95);\r\n    position: fixed;\r\n    top: 0;\r\n    left: 0;\r\n    margin: 0;\r\n    padding: 0;\r\n    transition: all 0.3s;\r\n}\r\n.modal-content {\r\n    width: auto;\r\n    box-sizing: border-box;\r\n    margin: 0.5rem;\r\n    padding: 2rem;\r\n    position: relative;\r\n    z-index: 11; /* places the modal dialog on top of overlay */\r\n    top: 0;\r\n    left: 0;\r\n    display: flex;\r\n    flex-direction: column;\r\n    background-color: white;\r\n    h5 {\r\n        margin-top: 0;\r\n    }\r\n    @media (min-width: $breakpoint-mobile) {\r\n        margin: auto;\r\n        max-width: 500px;\r\n    }\r\n}\r\n.modal-close {\r\n    position: absolute;\r\n    z-index: 12;\r\n    top: 0.5rem;\r\n    right: 0.5rem;\r\n}\r\n\r\n.user {\r\n    height: 20px;\r\n    line-height: 20px;\r\n    .user-username {\r\n        display: inline-block;\r\n        vertical-align: middle;\r\n    }\r\n    .user-avatar.thumbnail {\r\n        display: inline-block;\r\n        vertical-align: middle;\r\n        margin-right: 5px;\r\n    }\r\n}\r\n.user-avatar {\r\n    &.big img {\r\n        width: 144px;\r\n        height: 144px;\r\n    }\r\n    &.icon img {\r\n        width: 48px;\r\n        height: 48px;\r\n    }\r\n    &.thumbnail img {\r\n        width: 20px;\r\n        height: 20px;\r\n    }\r\n}\r\n"],"sourceRoot":""}]);
// Exports
/* harmony default export */ __webpack_exports__["default"] = (___CSS_LOADER_EXPORT___);


/***/ })

/******/ });
//# sourceMappingURL=app_bundle.js.map