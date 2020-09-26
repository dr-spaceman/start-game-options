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

/***/ "./assets/images/h1.png":
/*!******************************!*\
  !*** ./assets/images/h1.png ***!
  \******************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony default export */ __webpack_exports__["default"] = ("data:image/png;base64,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");

/***/ }),

/***/ "./assets/images/questionblock.png":
/*!*****************************************!*\
  !*** ./assets/images/questionblock.png ***!
  \*****************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony default export */ __webpack_exports__["default"] = ("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAwAAAANCAMAAACq939wAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAAxQTFRF/9uStiQA/7YA////12/UCQAAAAR0Uk5T////AEAqqfQAAAA9SURBVHjaVI3RDgAgCAKB/v+fk3BZ9+C8gRMaFiQcSojebRGSNSyq1d1IrviI00iak+iVSb5aQz+8bAEGAEBqAMyCXqpGAAAAAElFTkSuQmCC");

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
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(react__WEBPACK_IMPORTED_MODULE_0___default.a.Fragment, null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("h1", null, "Hello World!"), open && /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(react__WEBPACK_IMPORTED_MODULE_0___default.a.Fragment, null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("p", null, "Lorem ipsum dolor sit amet ", /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
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
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(react__WEBPACK_IMPORTED_MODULE_0___default.a.Fragment, null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_NavMenu_jsx__WEBPACK_IMPORTED_MODULE_1__["default"], null));
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
/* harmony import */ var _images_questionblock_png__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../images/questionblock.png */ "./assets/images/questionblock.png");



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
  const loginButtonStyle = {
    paddingLeft: 18,
    background: `url(${_images_questionblock_png__WEBPACK_IMPORTED_MODULE_2__["default"]}) no-repeat left center`
  };
  const loginButton = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: "/login.php",
    onClick: handleOpen,
    style: loginButtonStyle
  }, "Login");
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
    id: "login"
  }, userLink || loginButton, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_Modal_jsx__WEBPACK_IMPORTED_MODULE_1__["default"], {
    open: open,
    close: handleClose
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


function Modal(props) {
  const {
    children,
    open = true,
    close = null,
    timeout = 500,
    overlay = true
  } = props;
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(react_transition_group__WEBPACK_IMPORTED_MODULE_1__["CSSTransition"], {
    in: open,
    timeout: timeout,
    classNames: "modal",
    unmountOnExit: true
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
    className: "modal modal-container"
  }, overlay && /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
    className: "modal-overlay",
    role: "button",
    onClick: close,
    "aria-hidden": "true",
    "aria-label": "close"
  }), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
    className: "modal-content"
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
/* harmony import */ var react_icons__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! react-icons */ "./node_modules/react-icons/lib/esm/index.js");
/* harmony import */ var react_icons_bi__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! react-icons/bi */ "./node_modules/react-icons/bi/index.esm.js");
/* harmony import */ var _Modal_jsx__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./Modal.jsx */ "./assets/javascript/Modal.jsx");




function NavMenu(props) {
  const [open, setOpen] = react__WEBPACK_IMPORTED_MODULE_0___default.a.useState(false);

  const handleOpen = event => {
    event.preventDefault();
    setOpen(!open);
  };

  const handleClose = () => {
    setOpen(false);
  };

  const classname = open ? 'plain active' : 'plain inactive';
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
    id: "navmenu"
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("button", {
    type: "button",
    role: "switch",
    "aria-checked": open,
    id: "hamburger",
    className: classname,
    onClick: handleOpen
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(react_icons__WEBPACK_IMPORTED_MODULE_1__["IconContext"].Provider, {
    value: {
      size: '30px',
      color: 'white'
    }
  }, open ? /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(react_icons_bi__WEBPACK_IMPORTED_MODULE_2__["BiX"], null) : /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(react_icons_bi__WEBPACK_IMPORTED_MODULE_2__["BiMenu"], null))), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_Modal_jsx__WEBPACK_IMPORTED_MODULE_3__["default"], {
    open: open,
    close: handleClose
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("ul", null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("li", null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: "/games"
  }, "Games")), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("li", null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: "/people"
  }, "People")), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("li", null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
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
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("fieldset", {
    className: "inputwithlabel"
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("label", {
    htmlFor: "searchform"
  }, "Search:"), ' ', /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("input", {
    id: "searchform",
    type: "text",
    value: searchTerm,
    placeholder: "Search all the things",
    onChange: handleSearch
  }), ' ', /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("button", {
    type: "reset",
    onClick: () => setSearchTerm('')
  }, "Reset"), results.isError && /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("p", null, "Something went wrong"), results.isLoading ? /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("p", null, "Loading...") : /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(SearchResults, {
    results: results
  }));
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
/* harmony import */ var _images_h1_png__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../images/h1.png */ "./assets/images/h1.png");
/* harmony import */ var _images_twitter_sm_png__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../images/twitter_sm.png */ "./assets/images/twitter_sm.png");
/* harmony import */ var _images_footer_diorama_png__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../images/footer_diorama.png */ "./assets/images/footer_diorama.png");
// Imports





var ___CSS_LOADER_EXPORT___ = _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default()(true);
var ___CSS_LOADER_URL_REPLACEMENT_0___ = _node_modules_css_loader_dist_runtime_getUrl_js__WEBPACK_IMPORTED_MODULE_1___default()(_images_h1_png__WEBPACK_IMPORTED_MODULE_2__["default"]);
var ___CSS_LOADER_URL_REPLACEMENT_1___ = _node_modules_css_loader_dist_runtime_getUrl_js__WEBPACK_IMPORTED_MODULE_1___default()(_images_twitter_sm_png__WEBPACK_IMPORTED_MODULE_3__["default"]);
var ___CSS_LOADER_URL_REPLACEMENT_2___ = _node_modules_css_loader_dist_runtime_getUrl_js__WEBPACK_IMPORTED_MODULE_1___default()(_images_footer_diorama_png__WEBPACK_IMPORTED_MODULE_4__["default"]);
// Module
___CSS_LOADER_EXPORT___.push([module.i, ":root {\n  font: normal 100% sans-serif;\n  font-size: calc(100vw / 25);\n  color: white;\n}\n@media (min-width: 641px) {\n  :root {\n    font-size: calc(100vw / 70);\n  }\n}\n\nbody {\n  min-height: 100vh;\n  box-sizing: border-box;\n  padding: 0;\n  background-color: #18191a;\n  text-align: left;\n  vertical-align: top;\n}\n\na,\n.a {\n  color: #3399ff;\n  text-decoration: underline;\n  cursor: pointer;\n}\na:active,\n.a:active {\n  color: #6b3ea8;\n}\na:hover,\n.a:hover {\n  color: #66b3ff;\n  border-color: #66b3ff;\n}\n\nfieldset {\n  margin-left: 0;\n  margin-right: 0;\n  padding: 5px 10px 10px 10px;\n  border: 1px solid #ccc;\n}\n\nlegend {\n  color: #666;\n}\n\ninput[type=text],\ninput[type=password],\ntextarea,\nselect,\n.inputfield {\n  padding: 3px 1px 3px 2px;\n  margin-bottom: 1px;\n  border-width: 1px;\n  border-style: solid;\n  border-color: #666 #bbb #bbb #666;\n  background: white;\n  background: rgba(255, 255, 255, 0.7);\n  border-radius: 2px;\n  outline: none;\n}\n\ntextarea {\n  font-family: monospace;\n}\n\nselect {\n  padding: 2px;\n}\n\noptgroup {\n  padding-top: 2px;\n  font-weight: normal;\n  font-style: italic;\n  color: #777;\n  background-color: #eee;\n}\n\noptgroup > option {\n  padding-left: 20px;\n  background-color: #fff;\n  color: black;\n}\n\noptgroup > option:first-child {\n  margin-top: 2px;\n}\n\nbutton:not(.plain),\ninput[type=button],\ninput[type=submit],\ninput[type=reset],\n.faux-button {\n  padding: 3px 10px;\n  color: #444;\n  text-shadow: 0 -1px #dadada, 0 1px #eee;\n  background: #ddd;\n  background: -moz-linear-gradient(top, #eee 50%, #ddd 50%);\n  background: -webkit-gradient(linear, left top, left bottom, color-stop(50%, #eee), color-stop(50%, #ddd));\n  border-width: 1px;\n  border-style: solid;\n  border-color: #ddd #aaa #aaa #ddd;\n  border-radius: 2px;\n  box-shadow: 0 1px 0 rgba(0, 0, 0, 0.1);\n  cursor: pointer;\n}\nbutton:not(.plain):hover,\ninput[type=button]:hover,\ninput[type=submit]:hover,\ninput[type=reset]:hover,\n.faux-button:hover {\n  border-color: #777;\n}\nbutton:not(.plain):active, button:not(.plain).active,\ninput[type=button]:active,\ninput[type=button].active,\ninput[type=submit]:active,\ninput[type=submit].active,\ninput[type=reset]:active,\ninput[type=reset].active,\n.faux-button:active,\n.faux-button.active {\n  box-shadow: none;\n  background: #ddd;\n  border-color: #aaa #ccc #ccc #aaa;\n}\nbutton:not(.plain)[disabled=disabled],\ninput[type=button][disabled=disabled],\ninput[type=submit][disabled=disabled],\ninput[type=reset][disabled=disabled],\n.faux-button[disabled=disabled] {\n  color: #bbb;\n  cursor: not-allowed;\n}\nbutton:not(.plain).submit:hover,\ninput[type=button].submit:hover,\ninput[type=submit].submit:hover,\ninput[type=reset].submit:hover,\n.faux-button.submit:hover {\n  background: #00a264;\n  border-color: #016c43;\n  color: white;\n  text-shadow: none;\n}\nbutton:not(.plain).cancel:hover,\ninput[type=button].cancel:hover,\ninput[type=submit].cancel:hover,\ninput[type=reset].cancel:hover,\n.faux-button.cancel:hover {\n  background: #dd3333;\n  border-color: #a81c1c;\n  color: white;\n  text-shadow: none;\n}\n\nimg {\n  vertical-align: middle;\n}\n\nh1 {\n  font-weight: 300;\n  line-height: 1.1;\n  margin-top: 0;\n  margin-bottom: 0.75em;\n  color: whitesmoke;\n  text-shadow: 1px 1px 0 rgba(0, 0, 0, 0.2);\n}\n\nh2 {\n  margin-bottom: 0.5em;\n  font-weight: normal;\n  color: whitesmoke;\n}\n\nh3 {\n  margin: 1em 0 0.5em;\n}\n\nh4 {\n  margin: 1em 0 0.5em;\n}\n\nh5 {\n  margin: 1em 0 0.5em;\n  font-size: 1.25rem;\n}\n\nh6 {\n  margin: 1em 0 0.5em;\n}\n\n.grid-page {\n  display: grid;\n  grid-template-columns: [margin-start] 5% [narrow-gutter-start] 10% [wide-gutter-start] 70% [wide-gutter-end] 10% [narrow-gutter-end] 5% [margin-end];\n}\n.grid-page > * {\n  grid-column: narrow-gutter-start/narrow-gutter-end;\n}\n@media (min-width: 641px) {\n  .grid-page > * {\n    grid-column: wide-gutter-start/wide-gutter-end;\n  }\n}\n\nbody > header {\n  height: 55px;\n  margin-bottom: 1rem;\n  margin-right: 0;\n  margin-left: 0;\n  background-color: #18191a;\n}\n@media (min-width: 641px) {\n  body > header {\n    margin-bottom: 3.5rem;\n  }\n}\n\n#navmenu button {\n  position: fixed;\n  z-index: 9;\n  top: 1rem;\n  left: 1rem;\n  width: 305px;\n  height: 55px;\n  margin: 0;\n  padding: 0;\n  border: none;\n  background: transparent url(" + ___CSS_LOADER_URL_REPLACEMENT_0___ + ") no-repeat scroll 35px 0;\n  text-align: left;\n  cursor: pointer;\n}\n@media (min-width: 641px) {\n  #navmenu button {\n    top: 3.5rem;\n  }\n}\n@media (min-width: 641px) {\n  #navmenu button {\n    left: 3.5rem;\n  }\n}\n#navmenu button:hover, #navmenu button:active, #navmenu button.active {\n  background-position: 35px -90px;\n}\n@media (min-width: 640px) {\n  #navmenu button {\n    top: 1rem;\n    left: 1rem;\n    margin-left: -38px;\n  }\n}\n@media (min-width: 640px) and (min-width: 641px) {\n  #navmenu button {\n    top: 3.5rem;\n  }\n}\n@media (min-width: 640px) and (min-width: 641px) {\n  #navmenu button {\n    left: 3.5rem;\n  }\n}\n#navmenu .modal,\n#navmenu .modal-overlay {\n  z-index: 7;\n}\n#navmenu .modal-content {\n  z-index: 8;\n}\n\n#login .modal-content {\n  width: 225px;\n  margin-right: auto;\n  margin-left: auto;\n  background-color: transparent !important;\n}\n#login form {\n  margin-top: -1em;\n  display: flex;\n  flex-direction: column;\n  align-items: flex-start;\n}\n#login form > * {\n  margin-top: 1em;\n}\n#login form input {\n  width: 100%;\n  padding: 6px 0 6px 8px;\n  font-size: 14px;\n  color: #666;\n  background: white;\n  background: -moz-linear-gradient(top, #e0e0e0, white 7px);\n  background: -webkit-gradient(linear, left top, left bottom, color-stop(0%, #e0e0e0), color-stop(100%, white));\n  border-width: 0;\n  border-radius: 3px;\n}\n#login form button {\n  padding: 5px 15px;\n  font-size: 15px;\n  font-weight: bold;\n  border-width: 0;\n  box-shadow: 1px 1px 3px 1px black;\n  -moz-box-shadow: 1px 1px 3px 1px black;\n  -webkit-box-shadow: 1px 1px 3px 1px black;\n}\n#login form button:active {\n  margin: 1px 0 0 1px;\n  box-shadow: 0 0 3px 1px black;\n  -moz-box-shadow: 0 0 3px 1px black;\n  -webkit-box-shadow: 0 0 3px 1px black;\n}\n#login form button + button {\n  margin-left: 1em;\n}\n\nbody > #content {\n  padding-top: 0;\n  padding-right: 1rem;\n  padding-bottom: 1rem;\n  padding-left: 1rem;\n}\n@media (min-width: 641px) {\n  body > #content {\n    padding-right: 3.5rem;\n  }\n}\n@media (min-width: 641px) {\n  body > #content {\n    padding-bottom: 3.5rem;\n  }\n}\n@media (min-width: 641px) {\n  body > #content {\n    padding-left: 3.5rem;\n  }\n}\n\nbody > footer {\n  width: 100%;\n  box-sizing: border-box;\n  position: relative;\n  z-index: 1;\n  bottom: 0;\n  left: 0;\n  border-top: 1px solid #242526;\n  margin-top: 1rem;\n  padding: 1rem;\n  color: #999;\n}\n@media (min-width: 641px) {\n  body > footer {\n    margin-top: 3.5rem;\n  }\n}\n@media (min-width: 641px) {\n  body > footer {\n    padding: 3.5rem;\n  }\n}\nbody > footer a {\n  color: #aaa;\n}\nbody > footer a:hover {\n  color: #ccc;\n}\nbody > footer h5 {\n  margin: 0 0 5px;\n  font-size: 15px;\n}\nbody > footer ul {\n  margin: 0;\n  padding: 0;\n  list-style: none;\n}\nbody > footer ul li {\n  margin: 0;\n  padding: 0;\n}\nbody > footer .about,\nbody > footer .featured {\n  display: none;\n}\nbody > footer .about li a,\nbody > footer .featured li a {\n  display: block;\n  margin: 0 14px 7px 0;\n  font-size: 14px;\n  background-position: left center;\n  background-repeat: no-repeat;\n}\nbody > footer .about li a .link-twitter,\nbody > footer .featured li a .link-twitter {\n  padding-left: 20px;\n  background: url(" + ___CSS_LOADER_URL_REPLACEMENT_1___ + ") no-repeat left center;\n}\nbody > footer #diorama {\n  width: 100%;\n  height: 48px;\n}\nbody > footer #diorama div {\n  position: absolute;\n  background-image: url(" + ___CSS_LOADER_URL_REPLACEMENT_2___ + ");\n}\n\n.dark, #login .modal-content {\n  background-color: black;\n  color: white;\n}\n.dark a, #login .modal-content a {\n  color: white;\n}\n.dark a:hover, #login .modal-content a:hover {\n  color: lightgray;\n}\n\n.light {\n  background-color: white;\n  color: black;\n}\n.light a {\n  color: black;\n}\n.light a:hover {\n  color: #2e2e2e;\n}\n\n.red {\n  color: #dd3333;\n}\n.red:hover {\n  color: #f17878;\n}\n\n.modal {\n  position: fixed;\n  z-index: 10;\n  top: 0;\n  right: 0;\n  bottom: 0;\n  left: 0;\n  display: grid;\n}\n\n.modal-container.modal-enter {\n  opacity: 0;\n}\n.modal-container.modal-enter-active {\n  opacity: 1;\n  transition: all 500ms;\n}\n.modal-container.modal-exit {\n  opacity: 1;\n}\n.modal-container.modal-exit-active {\n  opacity: 0;\n  transition: opacity 500ms;\n}\n\n.modal-overlay {\n  width: 100%;\n  height: 100%;\n  z-index: 10;\n  /* places the modal overlay between the main page and the modal content*/\n  background-color: rgba(0, 0, 0, 0.95);\n  position: fixed;\n  top: 0;\n  left: 0;\n  margin: 0;\n  padding: 0;\n  transition: all 0.3s;\n}\n\n.modal-content {\n  width: auto;\n  margin: 0.5rem;\n  padding: 2rem;\n  position: relative;\n  z-index: 11;\n  /* places the modal dialog on top of overlay */\n  top: 0;\n  left: 0;\n  display: flex;\n  flex-direction: column;\n  background-color: white;\n}\n.modal-content h5 {\n  margin-top: 0;\n}\n@media (min-width: 640px) {\n  .modal-content {\n    margin: auto;\n    max-width: 500px;\n  }\n}\n\n.modal-close {\n  color: #aaa;\n  line-height: 50px;\n  font-size: 80%;\n  position: absolute;\n  right: 0;\n  text-align: center;\n  top: 0;\n  width: 70px;\n  text-decoration: none;\n}\n.modal-close:hover {\n  color: black;\n}\n\n.user {\n  height: 20px;\n  line-height: 20px;\n}\n.user .user-username {\n  display: inline-block;\n  vertical-align: middle;\n}\n.user .user-avatar.thumbnail {\n  display: inline-block;\n  vertical-align: middle;\n  margin-right: 5px;\n}\n\n.user-avatar.big img {\n  width: 144px;\n  height: 144px;\n}\n.user-avatar.icon img {\n  width: 48px;\n  height: 48px;\n}\n.user-avatar.thumbnail img {\n  width: 20px;\n  height: 20px;\n}", "",{"version":3,"sources":["webpack://app.scss"],"names":[],"mappings":"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","sourcesContent":["$blue: #3399ff;\r\n$lightblue: #66b3ff;\r\n$darkblue: #336699;\r\n$red: #dd3333;\r\n$green: #00a264;\r\n$purple: #6b3ea8;\r\n$color-body: rgb(24, 25, 26);\r\n$color-surface: rgb(36, 37, 38);\r\n$color-comment: rgb(58, 59, 60);\r\n\r\n$img-dir: \"../images\";\r\n\r\n// Breakpoint settings and methods\r\n\r\n$breakpoint-mobile: 640px;\r\n\r\n@mixin breakpoint($view) {\r\n    @if $view == mobile {\r\n        @media (max-width: $breakpoint-mobile) { @content; }\r\n    }\r\n    @else {\r\n        @media (min-width: $breakpoint-mobile + 1px) { @content; }\r\n    }\r\n}\r\n\r\n// Spacing settings and methods\r\n\r\n$spacing-mobile: 1rem;\r\n$spacing-monitor: 3.5rem;\r\n\r\n@mixin spacing($prop) {\r\n    #{$prop}: $spacing-mobile;\r\n    @include breakpoint(screen) {\r\n        #{$prop}: $spacing-monitor;\r\n    }\r\n}\r\n\r\n// Top Level (Global)\r\n\r\n:root {\r\n    font: normal 100% sans-serif;\r\n    font-size: calc(100vw / 25);\r\n    color: white;\r\n    @include breakpoint(screen) {\r\n        font-size: calc(100vw / 70);\r\n    }\r\n}\r\n\r\nbody {\r\n    min-height: 100vh;\r\n    box-sizing: border-box;\r\n    padding: 0;\r\n    background: {\r\n        color: $color-body;\r\n    }\r\n    text-align: left;\r\n    vertical-align: top;\r\n}\r\na,\r\n.a {\r\n    color: $blue;\r\n    text-decoration: underline;\r\n    cursor: pointer;\r\n    &:active {\r\n        color: $purple;\r\n    }\r\n    &:hover {\r\n        color: $lightblue;\r\n        border-color: $lightblue;\r\n    }\r\n}\r\n\r\nfieldset {\r\n    margin-left: 0;\r\n    margin-right: 0;\r\n    padding: 5px 10px 10px 10px;\r\n    border: 1px solid #ccc;\r\n}\r\nlegend {\r\n    color: #666;\r\n}\r\ninput[type=\"text\"],\r\ninput[type=\"password\"],\r\ntextarea,\r\nselect,\r\n.inputfield {\r\n    padding: 3px 1px 3px 2px;\r\n    margin-bottom: 1px;\r\n    border-width: 1px;\r\n    border-style: solid;\r\n    border-color: #666 #bbb #bbb #666;\r\n    background: white;\r\n    background: rgba(255, 255, 255, 0.7);\r\n    border-radius: 2px;\r\n    outline: none;\r\n}\r\ntextarea {\r\n    font-family: monospace;\r\n}\r\nselect {\r\n    padding: 2px;\r\n}\r\noptgroup {\r\n    padding-top: 2px;\r\n    font-weight: normal;\r\n    font-style: italic;\r\n    color: #777;\r\n    background-color: #eee;\r\n}\r\noptgroup > option {\r\n    padding-left: 20px;\r\n    background-color: #fff;\r\n    color: black;\r\n}\r\noptgroup > option:first-child {\r\n    margin-top: 2px;\r\n}\r\n\r\n// .button-blue {\r\n// \t@link-color: darken(@lightblue, 16%);\r\n// \tcolor:rgba(255,255,255,.93); text-shadow:0 -1px @link-color, 0 1px @lightblue;\r\n// \tbackground:@link-color; background:-moz-linear-gradient(top, @lightblue 50%, @link-color 50%); background:-webkit-gradient(linear, left top, left bottom, color-stop(50%,@lightblue), color-stop(50%,@link-color));\r\n// \tborder-color:darken(@lightblue, 10%) darken(@link-color, 5%) darken(@link-color, 5%) darken(@lightblue, 5%);\r\n// \tbox-shadow:0 1px 1px rgba(0,0,0,.2);\r\n// \t&:hover { border-color:darken(@link-color, 25%); }\r\n// \t&:active { box-shadow:none; background:darken(@lightblue, 5%); border-color:darken(@link-color, 12%) darken(@lightblue, 12%) darken(@lightblue, 12%) darken(@link-color, 12%); }\r\n// }\r\nbutton:not(.plain),\r\ninput[type=\"button\"],\r\ninput[type=\"submit\"],\r\ninput[type=\"reset\"],\r\n.faux-button {\r\n    padding: 3px 10px;\r\n    color: #444;\r\n    text-shadow: 0 -1px #dadada, 0 1px #eee;\r\n    background: #ddd;\r\n    background: -moz-linear-gradient(top, #eee 50%, #ddd 50%);\r\n    background: -webkit-gradient(linear, left top, left bottom, color-stop(50%, #eee), color-stop(50%, #ddd));\r\n    border-width: 1px;\r\n    border-style: solid;\r\n    border-color: #ddd #aaa #aaa #ddd;\r\n    border-radius: 2px;\r\n    box-shadow: 0 1px 0 rgba(0, 0, 0, 0.1);\r\n    cursor: pointer;\r\n    &:hover {\r\n        border-color: #777;\r\n    }\r\n    &:active,\r\n    &.active {\r\n        box-shadow: none;\r\n        background: #ddd;\r\n        border-color: #aaa #ccc #ccc #aaa;\r\n    }\r\n    &[disabled=\"disabled\"] {\r\n        color: #bbb;\r\n        cursor: not-allowed;\r\n    }\r\n    &.submit:hover {\r\n        background: $green;\r\n        border-color: #016c43;\r\n        color: white;\r\n        text-shadow: none;\r\n    }\r\n    &.cancel:hover {\r\n        background: $red;\r\n        border-color: darken($red, 15%);\r\n        color: white;\r\n        text-shadow: none;\r\n    }\r\n}\r\n\r\nimg {\r\n    vertical-align: middle;\r\n}\r\n\r\nh1 {\r\n    font-weight: 300;\r\n    line-height: 1.1;\r\n    margin-top: 0;\r\n    margin-bottom: 0.75em;\r\n    color: whitesmoke;\r\n    text-shadow: 1px 1px 0 rgba(0, 0, 0, 0.2);\r\n}\r\nh2 {\r\n    margin-bottom: 0.5em;\r\n    font-weight: normal;\r\n    color: whitesmoke;\r\n}\r\nh3 {\r\n    margin: 1em 0 0.5em;\r\n}\r\nh4 {\r\n    margin: 1em 0 0.5em;\r\n}\r\nh5 {\r\n    margin: 1em 0 0.5em;\r\n    font-size: 1.25rem;\r\n}\r\nh6 {\r\n    margin: 1em 0 0.5em;\r\n}\r\n\r\n// Page Layouts\r\n\r\n.grid-page {\r\n    display: grid;\r\n    grid-template-columns: [margin-start] 5% [narrow-gutter-start] 10% [wide-gutter-start] 70% [wide-gutter-end] 10% [narrow-gutter-end] 5% [margin-end];\r\n    & > * {\r\n        grid-column: narrow-gutter-start / narrow-gutter-end;\r\n        @include breakpoint(screen) {\r\n            grid-column: wide-gutter-start / wide-gutter-end;\r\n        }\r\n    }\r\n}\r\n\r\nbody > header {\r\n    height: 55px;\r\n    @include spacing(\"margin-bottom\");\r\n    margin-right: 0;\r\n    margin-left: 0;\r\n    background-color: $color-body;\r\n}\r\n#navmenu {\r\n    button {\r\n        position: fixed;\r\n        z-index: 9; // Behind modal overlay\r\n        @include spacing(\"top\");\r\n        @include spacing(\"left\");\r\n        width: 305px;\r\n        height: 55px;\r\n        margin: 0;\r\n        padding: 0;\r\n        border: none;\r\n        background: transparent url(\"#{$img-dir}/h1.png\") no-repeat scroll 35px 0;\r\n        text-align: left;\r\n        cursor: pointer;\r\n        &:hover,\r\n        &:active,\r\n        &.active {\r\n            background-position: 35px -90px;\r\n        }\r\n        @media (min-width: $breakpoint-mobile) {\r\n            @include spacing(\"top\");\r\n            @include spacing(\"left\");\r\n            margin-left: -38px; // hamburger:30px; bgimg padding:8px;\r\n        }\r\n    }\r\n    .modal,\r\n    .modal-overlay {\r\n        z-index: 7;\r\n    }\r\n    .modal-content {\r\n        z-index: 8;\r\n    }\r\n}\r\n#login {\r\n    .modal-content {\r\n        width: 225px;\r\n        margin-right: auto;\r\n        margin-left: auto;\r\n        @extend .dark;\r\n        background-color: transparent !important;\r\n    }\r\n    form {\r\n        margin-top: -1em;\r\n        display: flex;\r\n        flex-direction: column;\r\n        align-items: flex-start;\r\n        & > * {\r\n            margin-top: 1em;\r\n        }\r\n        input {\r\n            width: 100%;\r\n            padding: 6px 0 6px 8px;\r\n            font-size: 14px;\r\n            color: #666;\r\n            background: white;\r\n            background: -moz-linear-gradient(top, #e0e0e0, white 7px);\r\n            background: -webkit-gradient(\r\n                linear,\r\n                left top,\r\n                left bottom,\r\n                color-stop(0%, #e0e0e0),\r\n                color-stop(100%, white)\r\n            );\r\n            border-width: 0;\r\n            border-radius: 3px;\r\n        }\r\n        button {\r\n            padding: 5px 15px;\r\n            font-size: 15px;\r\n            font-weight: bold;\r\n            border-width: 0;\r\n            box-shadow: 1px 1px 3px 1px black;\r\n            -moz-box-shadow: 1px 1px 3px 1px black;\r\n            -webkit-box-shadow: 1px 1px 3px 1px black;\r\n            &:active {\r\n                margin: 1px 0 0 1px;\r\n                box-shadow: 0 0 3px 1px black;\r\n                -moz-box-shadow: 0 0 3px 1px black;\r\n                -webkit-box-shadow: 0 0 3px 1px black;\r\n            }\r\n            & + button {\r\n                margin-left: 1em;\r\n            }\r\n        }\r\n    }\r\n}\r\n\r\nbody > #content {\r\n    padding-top: 0;\r\n    @include spacing(\"padding-right\");\r\n    @include spacing(\"padding-bottom\");\r\n    @include spacing(\"padding-left\");\r\n}\r\n\r\nbody > footer {\r\n    width: 100%;\r\n    box-sizing: border-box;\r\n    position: relative;\r\n    z-index: 1;\r\n    bottom: 0;\r\n    left: 0;\r\n    border-top: 1px solid $color-surface;\r\n    @include spacing(\"margin-top\");\r\n    @include spacing(\"padding\");\r\n    color: #999;\r\n    a {\r\n        color: #aaa;\r\n    }\r\n    a:hover {\r\n        color: #ccc;\r\n    }\r\n    h5 {\r\n        margin: 0 0 5px;\r\n        font-size: 15px;\r\n    }\r\n    ul {\r\n        margin: 0;\r\n        padding: 0;\r\n        list-style: none;\r\n        li {\r\n            margin: 0;\r\n            padding: 0;\r\n        }\r\n    }\r\n    .about,\r\n    .featured {\r\n        display: none;\r\n        li {\r\n            a {\r\n                display: block;\r\n                margin: 0 14px 7px 0;\r\n                font-size: 14px;\r\n                background-position: left center;\r\n                background-repeat: no-repeat;\r\n                .link-twitter {\r\n                    padding-left: 20px;\r\n                    background: url(\"#{$img-dir}/twitter_sm.png\") no-repeat left center;\r\n                }\r\n            }\r\n        }\r\n    }\r\n    .featured ul li {\r\n    }\r\n    #diorama {\r\n        width: 100%;\r\n        height: 48px;\r\n        div {\r\n            position: absolute;\r\n            background-image: url(\"#{$img-dir}/footer_diorama.png\");\r\n        }\r\n    }\r\n}\r\n\r\n// Custom Classes\r\n\r\n.dark {\r\n    background-color: black;\r\n    color: white;\r\n    a {\r\n        color: white;\r\n        &:hover {\r\n            color: lightgray;\r\n        }\r\n    }\r\n}\r\n.light {\r\n    background-color: white;\r\n    color: black;\r\n    a {\r\n        color: black;\r\n        &:hover {\r\n            color: rgb(46, 46, 46);\r\n        }\r\n    }\r\n}\r\n.red {\r\n    color: $red;\r\n    &:hover {\r\n        color: #f17878;\r\n    }\r\n}\r\n\r\n.modal {\r\n    position: fixed;\r\n    z-index: 10;\r\n    top: 0;\r\n    right: 0;\r\n    bottom: 0;\r\n    left: 0;\r\n    display: grid;\r\n}\r\n.modal-container {\r\n    &.modal-enter {\r\n        opacity: 0;\r\n    }\r\n    &.modal-enter-active {\r\n        opacity: 1;\r\n        transition: all 500ms;\r\n    }\r\n    &.modal-exit {\r\n        opacity: 1;\r\n    }\r\n    &.modal-exit-active {\r\n        opacity: 0;\r\n        transition: opacity 500ms;\r\n    }\r\n}\r\n.modal-overlay {\r\n    width: 100%;\r\n    height: 100%;\r\n    z-index: 10; /* places the modal overlay between the main page and the modal content*/\r\n    background-color: rgba(0, 0, 0, 0.95);\r\n    position: fixed;\r\n    top: 0;\r\n    left: 0;\r\n    margin: 0;\r\n    padding: 0;\r\n    transition: all 0.3s;\r\n}\r\n.modal-content {\r\n    width: auto;\r\n    margin: 0.5rem;\r\n    padding: 2rem;\r\n    position: relative;\r\n    z-index: 11; /* places the modal dialog on top of overlay */\r\n    top: 0;\r\n    left: 0;\r\n    display: flex;\r\n    flex-direction: column;\r\n    background-color: white;\r\n    h5 {\r\n        margin-top: 0;\r\n    }\r\n    @media (min-width: $breakpoint-mobile) {\r\n        margin: auto;\r\n        max-width: 500px;\r\n    }\r\n}\r\n.modal-close {\r\n    color: #aaa;\r\n    line-height: 50px;\r\n    font-size: 80%;\r\n    position: absolute;\r\n    right: 0;\r\n    text-align: center;\r\n    top: 0;\r\n    width: 70px;\r\n    text-decoration: none;\r\n    &:hover {\r\n        color: black;\r\n    }\r\n}\r\n\r\n.user {\r\n    height: 20px;\r\n    line-height: 20px;\r\n    .user-username {\r\n        display: inline-block;\r\n        vertical-align: middle;\r\n    }\r\n    .user-avatar.thumbnail {\r\n        display: inline-block;\r\n        vertical-align: middle;\r\n        margin-right: 5px;\r\n    }\r\n}\r\n.user-avatar {\r\n    &.big img {\r\n        width: 144px;\r\n        height: 144px;\r\n    }\r\n    &.icon img {\r\n        width: 48px;\r\n        height: 48px;\r\n    }\r\n    &.thumbnail img {\r\n        width: 20px;\r\n        height: 20px;\r\n    }\r\n}\r\n"],"sourceRoot":""}]);
// Exports
/* harmony default export */ __webpack_exports__["default"] = (___CSS_LOADER_EXPORT___);


/***/ })

/******/ });
//# sourceMappingURL=app_bundle.js.map