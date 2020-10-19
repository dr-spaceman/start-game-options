(window["webpackJsonp"] = window["webpackJsonp"] || []).push([["TopNavUser"],{

/***/ "./browser/src/components/Dropdown.jsx":
/*!*********************************************!*\
  !*** ./browser/src/components/Dropdown.jsx ***!
  \*********************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var classnames__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! classnames */ "./node_modules/classnames/index.js");
/* harmony import */ var classnames__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(classnames__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _lib_match_component_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../lib/match-component.js */ "./browser/src/lib/match-component.js");
/* harmony import */ var _hooks_use_outside_click_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../hooks/use-outside-click.js */ "./browser/src/hooks/use-outside-click.js");
function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }





const isDropdownToggle = Object(_lib_match_component_js__WEBPACK_IMPORTED_MODULE_2__["default"])(DropdownToggle);
const isDropdownMenu = Object(_lib_match_component_js__WEBPACK_IMPORTED_MODULE_2__["default"])(DropdownMenu);

function Dropdown({
  className,
  children,
  ...props
}) {
  const [open, setOpen] = react__WEBPACK_IMPORTED_MODULE_0___default.a.useState(false);

  const handleToggle = () => setOpen(!open);

  const handleClose = () => setOpen(false);

  const ref = Object(_hooks_use_outside_click_js__WEBPACK_IMPORTED_MODULE_3__["default"])(handleClose);
  const classNames = classnames__WEBPACK_IMPORTED_MODULE_1___default()({
    className,
    dropdown: true,
    open
  });
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", _extends({
    ref: ref,
    className: classNames
  }, props), react__WEBPACK_IMPORTED_MODULE_0___default.a.Children.map(children, child => {
    if (! /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.isValidElement(child)) {
      return child;
    } // Button toggle dropdown menu


    if (isDropdownToggle(child)) {
      return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.cloneElement(child, {
        handleClick: handleToggle
      });
    }

    return child;
  }));
}

function DropdownToggle({
  className,
  children,
  handleClick
}) {
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("button", {
    className: `dropdown-toggle ${className}`,
    type: "button",
    onClick: handleClick,
    "aria-haspopup": "true",
    "aria-expanded": "true"
  }, children);
}

function DropdownMenu({
  className,
  children
}) {
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
    className: `dropdown-menu light ${className}`,
    role: "menu"
  }, children);
}

function DropdownItem({
  children
}) {
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
    className: "dropdown-item",
    role: "menuitem"
  }, children);
}

Dropdown.Toggle = DropdownToggle;
Dropdown.Menu = DropdownMenu;
Dropdown.Item = DropdownItem;
/* harmony default export */ __webpack_exports__["default"] = (Dropdown);

/***/ }),

/***/ "./browser/src/components/TopNavUser.jsx":
/*!***********************************************!*\
  !*** ./browser/src/components/TopNavUser.jsx ***!
  \***********************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return TopNavUser; });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _Dropdown_jsx__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Dropdown.jsx */ "./browser/src/components/Dropdown.jsx");
/* harmony import */ var _User_jsx__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./User.jsx */ "./browser/src/components/User.jsx");



console.log('<TopNavUser> has been lazy loaded!');
function TopNavUser({
  username
}) {
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_Dropdown_jsx__WEBPACK_IMPORTED_MODULE_1__["default"], {
    id: "login-user-dropdown"
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_Dropdown_jsx__WEBPACK_IMPORTED_MODULE_1__["default"].Toggle, {
    className: "access-button"
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_User_jsx__WEBPACK_IMPORTED_MODULE_2__["default"], {
    username: username,
    href: "",
    avatar: ""
  })), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_Dropdown_jsx__WEBPACK_IMPORTED_MODULE_1__["default"].Menu, null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_Dropdown_jsx__WEBPACK_IMPORTED_MODULE_1__["default"].Item, null, "foo"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_Dropdown_jsx__WEBPACK_IMPORTED_MODULE_1__["default"].Item, null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: `/~${username}`
  }, "Profile")), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_Dropdown_jsx__WEBPACK_IMPORTED_MODULE_1__["default"].Item, null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: `/~${username}/games`
  }, "Games")), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement(_Dropdown_jsx__WEBPACK_IMPORTED_MODULE_1__["default"].Item, null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("a", {
    href: "/login.php?do=logout"
  }, "Log out"))));
}

/***/ }),

/***/ "./browser/src/components/User.jsx":
/*!*****************************************!*\
  !*** ./browser/src/components/User.jsx ***!
  \*****************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return User; });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);

function User(props) {
  let {
    username,
    avatar,
    href
  } = props;

  if (href === undefined) {
    href = `~${username}`;
  }

  let tag;
  const tagChild = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("span", {
    className: "user-username"
  }, username);

  if (href) {
    tag = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement('a', {
      href,
      className: 'user-link'
    }, tagChild);
  } else {
    tag = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement('span', {
      className: 'user-link'
    }, tagChild);
  }

  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("span", {
    className: "user"
  }, tag);
}

/***/ }),

/***/ "./browser/src/hooks/use-outside-click.js":
/*!************************************************!*\
  !*** ./browser/src/hooks/use-outside-click.js ***!
  \************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return useOutsideClick; });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);

/**
 * Hook that listens for clicks outside of a reference.
 *
 * @param {Function} callback Function to execute on successful outside click
 *
 * @returns {Reference} Reference to "inside" element
 */

function useOutsideClick(callback) {
  const ref = react__WEBPACK_IMPORTED_MODULE_0___default.a.useRef();

  function handleClickOutside(event) {
    var _ref$current, _ref$current$contains;

    // If the click is registered outside the given ref, trigger cb
    if (!((_ref$current = ref.current) === null || _ref$current === void 0 ? void 0 : (_ref$current$contains = _ref$current.contains) === null || _ref$current$contains === void 0 ? void 0 : _ref$current$contains.call(_ref$current, event.target))) {
      console.log('Outside click detected', callback);
      callback(event);
    }
  }

  react__WEBPACK_IMPORTED_MODULE_0___default.a.useEffect(() => {
    // Bind the event listener
    document.addEventListener('click', handleClickOutside);
    console.log('Listening for outside click...');
    return function cleanup() {
      // Clean up before calling the effect again on the next render
      document.removeEventListener('click', handleClickOutside);
      console.log('Remove outside click handler');
    };
  }, [ref]);
  return ref;
}

function useOutsideClick_OLD(ref, callback = () => {}) {
  function handleClickOutside(event) {
    var _ref$current2, _ref$current2$contain;

    // If the click is registered outside the given ref, trigger cb
    if (!((_ref$current2 = ref.current) === null || _ref$current2 === void 0 ? void 0 : (_ref$current2$contain = _ref$current2.contains) === null || _ref$current2$contain === void 0 ? void 0 : _ref$current2$contain.call(_ref$current2, event.target))) {
      console.log('Outside click detected', callback);
      callback(event);
    }
  }

  react__WEBPACK_IMPORTED_MODULE_0___default.a.useEffect(() => {
    // Bind the event listener
    document.addEventListener('click', handleClickOutside);
    console.log('Listening for outside click...');
    return () => {
      // Clean up before calling the effect again on the next render
      document.removeEventListener('click', handleClickOutside);
      console.log('Remove outside click handler');
    };
  }, [callback]);
}

/***/ }),

/***/ "./browser/src/lib/match-component.js":
/*!********************************************!*\
  !*** ./browser/src/lib/match-component.js ***!
  \********************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
const matchComponent = Component => c => {
  // React Component
  if (c.type === Component) {
    return true;
  } // Matching componentType


  if (c.props && c.props.componentType === Component) {
    return true;
  }

  return false;
};

/* harmony default export */ __webpack_exports__["default"] = (matchComponent);

/***/ })

}]);
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9icm93c2VyL3NyYy9jb21wb25lbnRzL0Ryb3Bkb3duLmpzeCIsIndlYnBhY2s6Ly8vLi9icm93c2VyL3NyYy9jb21wb25lbnRzL1RvcE5hdlVzZXIuanN4Iiwid2VicGFjazovLy8uL2Jyb3dzZXIvc3JjL2NvbXBvbmVudHMvVXNlci5qc3giLCJ3ZWJwYWNrOi8vLy4vYnJvd3Nlci9zcmMvaG9va3MvdXNlLW91dHNpZGUtY2xpY2suanMiLCJ3ZWJwYWNrOi8vLy4vYnJvd3Nlci9zcmMvbGliL21hdGNoLWNvbXBvbmVudC5qcyJdLCJuYW1lcyI6WyJpc0Ryb3Bkb3duVG9nZ2xlIiwibWF0Y2hDb21wb25lbnQiLCJEcm9wZG93blRvZ2dsZSIsImlzRHJvcGRvd25NZW51IiwiRHJvcGRvd25NZW51IiwiRHJvcGRvd24iLCJjbGFzc05hbWUiLCJjaGlsZHJlbiIsInByb3BzIiwib3BlbiIsInNldE9wZW4iLCJSZWFjdCIsInVzZVN0YXRlIiwiaGFuZGxlVG9nZ2xlIiwiaGFuZGxlQ2xvc2UiLCJyZWYiLCJ1c2VPdXRzaWRlQ2xpY2siLCJjbGFzc05hbWVzIiwiY24iLCJkcm9wZG93biIsIkNoaWxkcmVuIiwibWFwIiwiY2hpbGQiLCJpc1ZhbGlkRWxlbWVudCIsImNsb25lRWxlbWVudCIsImhhbmRsZUNsaWNrIiwiRHJvcGRvd25JdGVtIiwiVG9nZ2xlIiwiTWVudSIsIkl0ZW0iLCJjb25zb2xlIiwibG9nIiwiVG9wTmF2VXNlciIsInVzZXJuYW1lIiwiVXNlciIsImF2YXRhciIsImhyZWYiLCJ1bmRlZmluZWQiLCJ0YWciLCJ0YWdDaGlsZCIsImNyZWF0ZUVsZW1lbnQiLCJjYWxsYmFjayIsInVzZVJlZiIsImhhbmRsZUNsaWNrT3V0c2lkZSIsImV2ZW50IiwiY3VycmVudCIsImNvbnRhaW5zIiwidGFyZ2V0IiwidXNlRWZmZWN0IiwiZG9jdW1lbnQiLCJhZGRFdmVudExpc3RlbmVyIiwiY2xlYW51cCIsInJlbW92ZUV2ZW50TGlzdGVuZXIiLCJ1c2VPdXRzaWRlQ2xpY2tfT0xEIiwiQ29tcG9uZW50IiwiYyIsInR5cGUiLCJjb21wb25lbnRUeXBlIl0sIm1hcHBpbmdzIjoiOzs7Ozs7Ozs7Ozs7Ozs7Ozs7O0FBQUE7QUFDQTtBQUNBO0FBQ0E7QUFFQSxNQUFNQSxnQkFBZ0IsR0FBR0MsdUVBQWMsQ0FBQ0MsY0FBRCxDQUF2QztBQUNBLE1BQU1DLGNBQWMsR0FBR0YsdUVBQWMsQ0FBQ0csWUFBRCxDQUFyQzs7QUFFQSxTQUFTQyxRQUFULENBQWtCO0FBQUVDLFdBQUY7QUFBYUMsVUFBYjtBQUF1QixLQUFHQztBQUExQixDQUFsQixFQUFxRDtBQUNqRCxRQUFNLENBQUNDLElBQUQsRUFBT0MsT0FBUCxJQUFrQkMsNENBQUssQ0FBQ0MsUUFBTixDQUFlLEtBQWYsQ0FBeEI7O0FBQ0EsUUFBTUMsWUFBWSxHQUFHLE1BQU1ILE9BQU8sQ0FBQyxDQUFDRCxJQUFGLENBQWxDOztBQUNBLFFBQU1LLFdBQVcsR0FBRyxNQUFNSixPQUFPLENBQUMsS0FBRCxDQUFqQzs7QUFFQSxRQUFNSyxHQUFHLEdBQUdDLDJFQUFlLENBQUNGLFdBQUQsQ0FBM0I7QUFFQSxRQUFNRyxVQUFVLEdBQUdDLGlEQUFFLENBQUM7QUFDbEJaLGFBRGtCO0FBRWxCYSxZQUFRLEVBQUUsSUFGUTtBQUdsQlY7QUFIa0IsR0FBRCxDQUFyQjtBQU1BLHNCQUNJO0FBQUssT0FBRyxFQUFFTSxHQUFWO0FBQWUsYUFBUyxFQUFFRTtBQUExQixLQUEwQ1QsS0FBMUMsR0FFS0csNENBQUssQ0FBQ1MsUUFBTixDQUFlQyxHQUFmLENBQW1CZCxRQUFuQixFQUE4QmUsS0FBRCxJQUFXO0FBQ3JDLFFBQUksZUFBQ1gsNENBQUssQ0FBQ1ksY0FBTixDQUFxQkQsS0FBckIsQ0FBTCxFQUFrQztBQUM5QixhQUFPQSxLQUFQO0FBQ0gsS0FIb0MsQ0FLckM7OztBQUNBLFFBQUl0QixnQkFBZ0IsQ0FBQ3NCLEtBQUQsQ0FBcEIsRUFBNkI7QUFDekIsMEJBQU9YLDRDQUFLLENBQUNhLFlBQU4sQ0FB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LEtBQTVCLEVBQW1DO0FBQUE7O0FBQy9CO0FBQ0EsUUFBSSxtQkFBQzdCLEdBQUcsQ0FBQzhCLE9BQUwsMkVBQUMsY0FBYUMsUUFBZCwwREFBQywwQ0FBd0JGLEtBQUssQ0FBQ0csTUFBOUIsQ0FBRCxDQUFKLEVBQTRDO0FBQ3hDakIsYUFBTyxDQUFDQyxHQUFSLENBQVksd0JBQVosRUFBc0NVLFFBQXRDO0FBQ0FBLGNBQVEsQ0FBQ0csS0FBRCxDQUFSO0FBQ0g7QUFDSjs7QUFFRGpDLDhDQUFLLENBQUNxQyxTQUFOLENBQWdCLE1BQU07QUFDbEI7QUFDQUMsWUFBUSxDQUFDQyxnQkFBVCxDQUEwQixPQUExQixFQUFtQ1Asa0JBQW5DO0FBQ0FiLFdBQU8sQ0FBQ0MsR0FBUixDQUFZLGdDQUFaO0FBQ0EsV0FBTyxNQUFNO0FBQ1Q7QUFDQWtCLGNBQVEsQ0FBQ0csbUJBQVQsQ0FBNkIsT0FBN0IsRUFBc0NULGtCQUF0QztBQUNBYixhQUFPLENBQUNDLEdBQVIsQ0FBWSw4QkFBWjtBQUNILEtBSkQ7QUFLSCxHQVRELEVBU0csQ0FBQ1UsUUFBRCxDQVRIO0FBVUgsQzs7Ozs7Ozs7Ozs7O0FDckREO0FBQUEsTUFBTXhDLGNBQWMsR0FBSXFELFNBQUQsSUFBZ0JDLENBQUQsSUFBTztBQUN6QztBQUNBLE1BQUlBLENBQUMsQ0FBQ0MsSUFBRixLQUFXRixTQUFmLEVBQTBCO0FBQ3RCLFdBQU8sSUFBUDtBQUNILEdBSndDLENBTXpDOzs7QUFDQSxNQUFJQyxDQUFDLENBQUMvQyxLQUFGLElBQVcrQyxDQUFDLENBQUMvQyxLQUFGLENBQVFpRCxhQUFSLEtBQTBCSCxTQUF6QyxFQUFvRDtBQUNoRCxXQUFPLElBQVA7QUFDSDs7QUFFRCxTQUFPLEtBQVA7QUFDSCxDQVpEOztBQWNlckQsNkVBQWYsRSIsImZpbGUiOiJUb3BOYXZVc2VyX2J1bmRsZS5qcyIsInNvdXJjZXNDb250ZW50IjpbImltcG9ydCBSZWFjdCBmcm9tICdyZWFjdCc7XG5pbXBvcnQgY24gZnJvbSAnY2xhc3NuYW1lcyc7XG5pbXBvcnQgbWF0Y2hDb21wb25lbnQgZnJvbSAnLi4vbGliL21hdGNoLWNvbXBvbmVudC5qcyc7XG5pbXBvcnQgdXNlT3V0c2lkZUNsaWNrIGZyb20gJy4uL2hvb2tzL3VzZS1vdXRzaWRlLWNsaWNrLmpzJztcblxuY29uc3QgaXNEcm9wZG93blRvZ2dsZSA9IG1hdGNoQ29tcG9uZW50KERyb3Bkb3duVG9nZ2xlKTtcbmNvbnN0IGlzRHJvcGRvd25NZW51ID0gbWF0Y2hDb21wb25lbnQoRHJvcGRvd25NZW51KTtcblxuZnVuY3Rpb24gRHJvcGRvd24oeyBjbGFzc05hbWUsIGNoaWxkcmVuLCAuLi5wcm9wcyB9KSB7XG4gICAgY29uc3QgW29wZW4sIHNldE9wZW5dID0gUmVhY3QudXNlU3RhdGUoZmFsc2UpO1xuICAgIGNvbnN0IGhhbmRsZVRvZ2dsZSA9ICgpID0+IHNldE9wZW4oIW9wZW4pO1xuICAgIGNvbnN0IGhhbmRsZUNsb3NlID0gKCkgPT4gc2V0T3BlbihmYWxzZSk7XG5cbiAgICBjb25zdCByZWYgPSB1c2VPdXRzaWRlQ2xpY2soaGFuZGxlQ2xvc2UpO1xuXG4gICAgY29uc3QgY2xhc3NOYW1lcyA9IGNuKHtcbiAgICAgICAgY2xhc3NOYW1lLFxuICAgICAgICBkcm9wZG93bjogdHJ1ZSxcbiAgICAgICAgb3BlbixcbiAgICB9KTtcblxuICAgIHJldHVybiAoXG4gICAgICAgIDxkaXYgcmVmPXtyZWZ9IGNsYXNzTmFtZT17Y2xhc3NOYW1lc30gey4uLnByb3BzfT5cbiAgICAgICAgICAgIHsvKiBNYXAgY2hpbGRyZW4gJiBpbmplY3QgbGlzdGVuZXJzICovfVxuICAgICAgICAgICAge1JlYWN0LkNoaWxkcmVuLm1hcChjaGlsZHJlbiwgKGNoaWxkKSA9PiB7XG4gICAgICAgICAgICAgICAgaWYgKCFSZWFjdC5pc1ZhbGlkRWxlbWVudChjaGlsZCkpIHtcbiAgICAgICAgICAgICAgICAgICAgcmV0dXJuIGNoaWxkO1xuICAgICAgICAgICAgICAgIH1cblxuICAgICAgICAgICAgICAgIC8vIEJ1dHRvbiB0b2dnbGUgZHJvcGRvd24gbWVudVxuICAgICAgICAgICAgICAgIGlmIChpc0Ryb3Bkb3duVG9nZ2xlKGNoaWxkKSkge1xuICAgICAgICAgICAgICAgICAgICByZXR1cm4gUmVhY3QuY2xvbmVFbGVtZW50KGNoaWxkLCB7XG4gICAgICAgICAgICAgICAgICAgICAgICBoYW5kbGVDbGljazogaGFuZGxlVG9nZ2xlLFxuICAgICAgICAgICAgICAgICAgICB9KTtcbiAgICAgICAgICAgICAgICB9XG5cbiAgICAgICAgICAgICAgICByZXR1cm4gY2hpbGQ7XG4gICAgICAgICAgICB9KX1cbiAgICAgICAgPC9kaXY+XG4gICAgKTtcbn1cblxuZnVuY3Rpb24gRHJvcGRvd25Ub2dnbGUoeyBjbGFzc05hbWUsIGNoaWxkcmVuLCBoYW5kbGVDbGljayB9KSB7XG4gICAgcmV0dXJuIChcbiAgICAgICAgPGJ1dHRvbiBjbGFzc05hbWU9e2Bkcm9wZG93bi10b2dnbGUgJHtjbGFzc05hbWV9YH0gdHlwZT1cImJ1dHRvblwiIG9uQ2xpY2s9e2hhbmRsZUNsaWNrfSBhcmlhLWhhc3BvcHVwPVwidHJ1ZVwiIGFyaWEtZXhwYW5kZWQ9XCJ0cnVlXCI+XG4gICAgICAgICAgICB7Y2hpbGRyZW59XG4gICAgICAgIDwvYnV0dG9uPlxuICAgICk7XG59XG5cbmZ1bmN0aW9uIERyb3Bkb3duTWVudSh7IGNsYXNzTmFtZSwgY2hpbGRyZW4gfSkge1xuICAgIHJldHVybiAoXG4gICAgICAgIDxkaXYgY2xhc3NOYW1lPXtgZHJvcGRvd24tbWVudSBsaWdodCAke2NsYXNzTmFtZX1gfSByb2xlPVwibWVudVwiPlxuICAgICAgICAgICAge2NoaWxkcmVufVxuICAgICAgICA8L2Rpdj5cbiAgICApO1xufVxuXG5mdW5jdGlvbiBEcm9wZG93bkl0ZW0oeyBjaGlsZHJlbiB9KSB7XG4gICAgcmV0dXJuIChcbiAgICAgICAgPGRpdiBjbGFzc05hbWU9XCJkcm9wZG93bi1pdGVtXCIgcm9sZT1cIm1lbnVpdGVtXCI+e2NoaWxkcmVufTwvZGl2PlxuICAgICk7XG59XG5cbkRyb3Bkb3duLlRvZ2dsZSA9IERyb3Bkb3duVG9nZ2xlO1xuRHJvcGRvd24uTWVudSA9IERyb3Bkb3duTWVudTtcbkRyb3Bkb3duLkl0ZW0gPSBEcm9wZG93bkl0ZW07XG5cbmV4cG9ydCBkZWZhdWx0IERyb3Bkb3duO1xuIiwiaW1wb3J0IFJlYWN0IGZyb20gJ3JlYWN0JztcbmltcG9ydCBEcm9wZG93biBmcm9tICcuL0Ryb3Bkb3duLmpzeCc7XG5pbXBvcnQgVXNlciBmcm9tICcuL1VzZXIuanN4JztcblxuY29uc29sZS5sb2coJzxUb3BOYXZVc2VyPiBoYXMgYmVlbiBsYXp5IGxvYWRlZCEnKTtcblxuZXhwb3J0IGRlZmF1bHQgZnVuY3Rpb24gVG9wTmF2VXNlcih7IHVzZXJuYW1lIH0pIHtcbiAgICByZXR1cm4gKFxuICAgICAgICA8RHJvcGRvd24gaWQ9XCJsb2dpbi11c2VyLWRyb3Bkb3duXCI+XG4gICAgICAgICAgICA8RHJvcGRvd24uVG9nZ2xlIGNsYXNzTmFtZT1cImFjY2Vzcy1idXR0b25cIj5cbiAgICAgICAgICAgICAgICA8VXNlciB1c2VybmFtZT17dXNlcm5hbWV9IGhyZWY9XCJcIiBhdmF0YXI9XCJcIiAvPlxuICAgICAgICAgICAgPC9Ecm9wZG93bi5Ub2dnbGU+XG4gICAgICAgICAgICA8RHJvcGRvd24uTWVudT5cbiAgICAgICAgICAgICAgICA8RHJvcGRvd24uSXRlbT5mb288L0Ryb3Bkb3duLkl0ZW0+XG4gICAgICAgICAgICAgICAgPERyb3Bkb3duLkl0ZW0+PGEgaHJlZj17YC9+JHt1c2VybmFtZX1gfT5Qcm9maWxlPC9hPjwvRHJvcGRvd24uSXRlbT5cbiAgICAgICAgICAgICAgICA8RHJvcGRvd24uSXRlbT48YSBocmVmPXtgL34ke3VzZXJuYW1lfS9nYW1lc2B9PkdhbWVzPC9hPjwvRHJvcGRvd24uSXRlbT5cbiAgICAgICAgICAgICAgICA8RHJvcGRvd24uSXRlbT48YSBocmVmPVwiL2xvZ2luLnBocD9kbz1sb2dvdXRcIj5Mb2cgb3V0PC9hPjwvRHJvcGRvd24uSXRlbT5cbiAgICAgICAgICAgIDwvRHJvcGRvd24uTWVudT5cbiAgICAgICAgPC9Ecm9wZG93bj5cbiAgICApO1xufVxuIiwiaW1wb3J0IFJlYWN0IGZyb20gJ3JlYWN0JztcblxuZXhwb3J0IGRlZmF1bHQgZnVuY3Rpb24gVXNlcihwcm9wcykge1xuICAgIGxldCB7IHVzZXJuYW1lLCBhdmF0YXIsIGhyZWYgfSA9IHByb3BzO1xuXG4gICAgaWYgKGhyZWYgPT09IHVuZGVmaW5lZCkge1xuICAgICAgICBocmVmID0gYH4ke3VzZXJuYW1lfWA7XG4gICAgfVxuXG4gICAgbGV0IHRhZztcbiAgICBjb25zdCB0YWdDaGlsZCA9IDxzcGFuIGNsYXNzTmFtZT1cInVzZXItdXNlcm5hbWVcIj57dXNlcm5hbWV9PC9zcGFuPjtcbiAgICBpZiAoaHJlZikge1xuICAgICAgICB0YWcgPSBSZWFjdC5jcmVhdGVFbGVtZW50KCdhJywgeyBocmVmLCBjbGFzc05hbWU6ICd1c2VyLWxpbmsnIH0sIHRhZ0NoaWxkKTtcbiAgICB9IGVsc2Uge1xuICAgICAgICB0YWcgPSBSZWFjdC5jcmVhdGVFbGVtZW50KCdzcGFuJywgeyBjbGFzc05hbWU6ICd1c2VyLWxpbmsnIH0sIHRhZ0NoaWxkKTtcbiAgICB9XG5cbiAgICByZXR1cm4gKFxuICAgICAgICA8c3BhbiBjbGFzc05hbWU9XCJ1c2VyXCI+XG4gICAgICAgICAgICB7dGFnfVxuICAgICAgICA8L3NwYW4+XG4gICAgKTtcbn1cbiIsImltcG9ydCBSZWFjdCBmcm9tICdyZWFjdCc7XG5cbi8qKlxuICogSG9vayB0aGF0IGxpc3RlbnMgZm9yIGNsaWNrcyBvdXRzaWRlIG9mIGEgcmVmZXJlbmNlLlxuICpcbiAqIEBwYXJhbSB7RnVuY3Rpb259IGNhbGxiYWNrIEZ1bmN0aW9uIHRvIGV4ZWN1dGUgb24gc3VjY2Vzc2Z1bCBvdXRzaWRlIGNsaWNrXG4gKlxuICogQHJldHVybnMge1JlZmVyZW5jZX0gUmVmZXJlbmNlIHRvIFwiaW5zaWRlXCIgZWxlbWVudFxuICovXG5leHBvcnQgZGVmYXVsdCBmdW5jdGlvbiB1c2VPdXRzaWRlQ2xpY2soY2FsbGJhY2spIHtcbiAgICBjb25zdCByZWYgPSBSZWFjdC51c2VSZWYoKTtcblxuICAgIGZ1bmN0aW9uIGhhbmRsZUNsaWNrT3V0c2lkZShldmVudCkge1xuICAgICAgICAvLyBJZiB0aGUgY2xpY2sgaXMgcmVnaXN0ZXJlZCBvdXRzaWRlIHRoZSBnaXZlbiByZWYsIHRyaWdnZXIgY2JcbiAgICAgICAgaWYgKCFyZWYuY3VycmVudD8uY29udGFpbnM/LihldmVudC50YXJnZXQpKSB7XG4gICAgICAgICAgICBjb25zb2xlLmxvZygnT3V0c2lkZSBjbGljayBkZXRlY3RlZCcsIGNhbGxiYWNrKTtcbiAgICAgICAgICAgIGNhbGxiYWNrKGV2ZW50KTtcbiAgICAgICAgfVxuICAgIH1cblxuICAgIFJlYWN0LnVzZUVmZmVjdCgoKSA9PiB7XG4gICAgICAgIC8vIEJpbmQgdGhlIGV2ZW50IGxpc3RlbmVyXG4gICAgICAgIGRvY3VtZW50LmFkZEV2ZW50TGlzdGVuZXIoJ2NsaWNrJywgaGFuZGxlQ2xpY2tPdXRzaWRlKTtcbiAgICAgICAgY29uc29sZS5sb2coJ0xpc3RlbmluZyBmb3Igb3V0c2lkZSBjbGljay4uLicpO1xuICAgICAgICByZXR1cm4gZnVuY3Rpb24gY2xlYW51cCgpIHtcbiAgICAgICAgICAgIC8vIENsZWFuIHVwIGJlZm9yZSBjYWxsaW5nIHRoZSBlZmZlY3QgYWdhaW4gb24gdGhlIG5leHQgcmVuZGVyXG4gICAgICAgICAgICBkb2N1bWVudC5yZW1vdmVFdmVudExpc3RlbmVyKCdjbGljaycsIGhhbmRsZUNsaWNrT3V0c2lkZSk7XG4gICAgICAgICAgICBjb25zb2xlLmxvZygnUmVtb3ZlIG91dHNpZGUgY2xpY2sgaGFuZGxlcicpO1xuICAgICAgICB9O1xuICAgIH0sIFtyZWZdKTtcblxuICAgIHJldHVybiByZWY7XG59XG5cbmZ1bmN0aW9uIHVzZU91dHNpZGVDbGlja19PTEQocmVmLCBjYWxsYmFjayA9ICgpID0+IHt9KSB7XG4gICAgZnVuY3Rpb24gaGFuZGxlQ2xpY2tPdXRzaWRlKGV2ZW50KSB7XG4gICAgICAgIC8vIElmIHRoZSBjbGljayBpcyByZWdpc3RlcmVkIG91dHNpZGUgdGhlIGdpdmVuIHJlZiwgdHJpZ2dlciBjYlxuICAgICAgICBpZiAoIXJlZi5jdXJyZW50Py5jb250YWlucz8uKGV2ZW50LnRhcmdldCkpIHtcbiAgICAgICAgICAgIGNvbnNvbGUubG9nKCdPdXRzaWRlIGNsaWNrIGRldGVjdGVkJywgY2FsbGJhY2spO1xuICAgICAgICAgICAgY2FsbGJhY2soZXZlbnQpO1xuICAgICAgICB9XG4gICAgfVxuXG4gICAgUmVhY3QudXNlRWZmZWN0KCgpID0+IHtcbiAgICAgICAgLy8gQmluZCB0aGUgZXZlbnQgbGlzdGVuZXJcbiAgICAgICAgZG9jdW1lbnQuYWRkRXZlbnRMaXN0ZW5lcignY2xpY2snLCBoYW5kbGVDbGlja091dHNpZGUpO1xuICAgICAgICBjb25zb2xlLmxvZygnTGlzdGVuaW5nIGZvciBvdXRzaWRlIGNsaWNrLi4uJyk7XG4gICAgICAgIHJldHVybiAoKSA9PiB7XG4gICAgICAgICAgICAvLyBDbGVhbiB1cCBiZWZvcmUgY2FsbGluZyB0aGUgZWZmZWN0IGFnYWluIG9uIHRoZSBuZXh0IHJlbmRlclxuICAgICAgICAgICAgZG9jdW1lbnQucmVtb3ZlRXZlbnRMaXN0ZW5lcignY2xpY2snLCBoYW5kbGVDbGlja091dHNpZGUpO1xuICAgICAgICAgICAgY29uc29sZS5sb2coJ1JlbW92ZSBvdXRzaWRlIGNsaWNrIGhhbmRsZXInKTtcbiAgICAgICAgfTtcbiAgICB9LCBbY2FsbGJhY2tdKTtcbn1cbiIsImNvbnN0IG1hdGNoQ29tcG9uZW50ID0gKENvbXBvbmVudCkgPT4gKGMpID0+IHtcclxuICAgIC8vIFJlYWN0IENvbXBvbmVudFxyXG4gICAgaWYgKGMudHlwZSA9PT0gQ29tcG9uZW50KSB7XHJcbiAgICAgICAgcmV0dXJuIHRydWU7XHJcbiAgICB9XHJcblxyXG4gICAgLy8gTWF0Y2hpbmcgY29tcG9uZW50VHlwZVxyXG4gICAgaWYgKGMucHJvcHMgJiYgYy5wcm9wcy5jb21wb25lbnRUeXBlID09PSBDb21wb25lbnQpIHtcclxuICAgICAgICByZXR1cm4gdHJ1ZTtcclxuICAgIH1cclxuXHJcbiAgICByZXR1cm4gZmFsc2U7XHJcbn07XHJcblxyXG5leHBvcnQgZGVmYXVsdCBtYXRjaENvbXBvbmVudDsiXSwic291cmNlUm9vdCI6IiJ9