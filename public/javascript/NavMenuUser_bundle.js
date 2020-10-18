(window["webpackJsonp"] = window["webpackJsonp"] || []).push([["NavMenuUser"],{

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
/* harmony import */ var _lib_use_outside_click_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../lib/use-outside-click.js */ "./browser/src/lib/use-outside-click.js");
function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }





const isDropdownToggle = Object(_lib_match_component_js__WEBPACK_IMPORTED_MODULE_2__["default"])(DropdownToggle);
const isDropdownMenu = Object(_lib_match_component_js__WEBPACK_IMPORTED_MODULE_2__["default"])(DropdownMenu);

function Dropdown(props) {
  const {
    className,
    children,
    ...rest
  } = props;
  const [open, setOpen] = react__WEBPACK_IMPORTED_MODULE_0___default.a.useState(false);

  const handleToggle = () => setOpen(!open);

  const handleClose = () => setOpen(false);

  const classnames = classnames__WEBPACK_IMPORTED_MODULE_1___default()({
    className,
    dropdown: true,
    open
  }); // Event listener is always active... problem?

  const wrapperRef = react__WEBPACK_IMPORTED_MODULE_0___default.a.useRef(null);
  Object(_lib_use_outside_click_js__WEBPACK_IMPORTED_MODULE_3__["default"])(wrapperRef, handleClose);
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", _extends({
    ref: wrapperRef,
    className: classnames
  }, rest), react__WEBPACK_IMPORTED_MODULE_0___default.a.Children.map(children, child => {
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

/***/ }),

/***/ "./browser/src/lib/use-outside-click.js":
/*!**********************************************!*\
  !*** ./browser/src/lib/use-outside-click.js ***!
  \**********************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return useOutsideClick; });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);

/**
 * Hook that alerts clicks outside of the passed ref
 */

function useOutsideClick(ref, callback) {
  react__WEBPACK_IMPORTED_MODULE_0___default.a.useEffect(() => {
    /**
     * Alert if clicked on outside of element
     */
    function handleClickOutside(event) {
      if (ref.current && !ref.current.contains(event.target)) {
        console.log('outside click detected', callback);
        callback();
      }
    } // Bind the event listener


    document.addEventListener('mousedown', handleClickOutside);
    return () => {
      // Clean up before calling the effect again on the next render
      document.removeEventListener('mousedown', handleClickOutside);
    };
  }, [ref]);
}
/**
 * Example use:
 * Component that alerts if you click outside of it
 */

function MyComponent(props) {
  const wrapperRef = useRef(null);
  useOutsideClick(wrapperRef);
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default.a.createElement("div", {
    ref: wrapperRef
  }, props.children);
}

/***/ })

}]);
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9icm93c2VyL3NyYy9jb21wb25lbnRzL0Ryb3Bkb3duLmpzeCIsIndlYnBhY2s6Ly8vLi9icm93c2VyL3NyYy9jb21wb25lbnRzL1RvcE5hdlVzZXIuanN4Iiwid2VicGFjazovLy8uL2Jyb3dzZXIvc3JjL2NvbXBvbmVudHMvVXNlci5qc3giLCJ3ZWJwYWNrOi8vLy4vYnJvd3Nlci9zcmMvbGliL21hdGNoLWNvbXBvbmVudC5qcyIsIndlYnBhY2s6Ly8vLi9icm93c2VyL3NyYy9saWIvdXNlLW91dHNpZGUtY2xpY2suanMiXSwibmFtZXMiOlsiaXNEcm9wZG93blRvZ2dsZSIsIm1hdGNoQ29tcG9uZW50IiwiRHJvcGRvd25Ub2dnbGUiLCJpc0Ryb3Bkb3duTWVudSIsIkRyb3Bkb3duTWVudSIsIkRyb3Bkb3duIiwicHJvcHMiLCJjbGFzc05hbWUiLCJjaGlsZHJlbiIsInJlc3QiLCJvcGVuIiwic2V0T3BlbiIsIlJlYWN0IiwidXNlU3RhdGUiLCJoYW5kbGVUb2dnbGUiLCJoYW5kbGVDbG9zZSIsImNsYXNzbmFtZXMiLCJjbGFzc05hbWVzIiwiZHJvcGRvd24iLCJ3cmFwcGVyUmVmIiwidXNlUmVmIiwidXNlT3V0c2lkZUNsaWNrIiwiQ2hpbGRyZW4iLCJtYXAiLCJjaGlsZCIsImlzVmFsaWRFbGVtZW50IiwiY2xvbmVFbGVtZW50IiwiaGFuZGxlQ2xpY2siLCJEcm9wZG93bkl0ZW0iLCJUb2dnbGUiLCJNZW51IiwiSXRlbSIsImNvbnNvbGUiLCJsb2ciLCJUb3BOYXZVc2VyIiwidXNlcm5hbWUiLCJVc2VyIiwiYXZhdGFyIiwiaHJlZiIsInVuZGVmaW5lZCIsInRhZyIsInRhZ0NoaWxkIiwiY3JlYXRlRWxlbWVudCIsIkNvbXBvbmVudCIsImMiLCJ0eXBlIiwiY29tcG9uZW50VHlwZSIsInJlZiIsImNhbGxiYWNrIiwidXNlRWZmZWN0IiwiaGFuZGxlQ2xpY2tPdXRzaWRlIiwiZXZlbnQiLCJjdXJyZW50IiwiY29udGFpbnMiLCJ0YXJnZXQiLCJkb2N1bWVudCIsImFkZEV2ZW50TGlzdGVuZXIiLCJyZW1vdmVFdmVudExpc3RlbmVyIiwiTXlDb21wb25lbnQiXSwibWFwcGluZ3MiOiI7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7QUFBQTtBQUNBO0FBQ0E7QUFDQTtBQUVBLE1BQU1BLGdCQUFnQixHQUFHQyx1RUFBYyxDQUFDQyxjQUFELENBQXZDO0FBQ0EsTUFBTUMsY0FBYyxHQUFHRix1RUFBYyxDQUFDRyxZQUFELENBQXJDOztBQUVBLFNBQVNDLFFBQVQsQ0FBa0JDLEtBQWxCLEVBQXlCO0FBQ3JCLFFBQU07QUFBRUMsYUFBRjtBQUFhQyxZQUFiO0FBQXVCLE9BQUdDO0FBQTFCLE1BQW1DSCxLQUF6QztBQUVBLFFBQU0sQ0FBQ0ksSUFBRCxFQUFPQyxPQUFQLElBQWtCQyw0Q0FBSyxDQUFDQyxRQUFOLENBQWUsS0FBZixDQUF4Qjs7QUFDQSxRQUFNQyxZQUFZLEdBQUcsTUFBTUgsT0FBTyxDQUFDLENBQUNELElBQUYsQ0FBbEM7O0FBQ0EsUUFBTUssV0FBVyxHQUFHLE1BQU1KLE9BQU8sQ0FBQyxLQUFELENBQWpDOztBQUVBLFFBQU1LLFVBQVUsR0FBR0MsaURBQVUsQ0FBQztBQUMxQlYsYUFEMEI7QUFFMUJXLFlBQVEsRUFBRSxJQUZnQjtBQUcxQlI7QUFIMEIsR0FBRCxDQUE3QixDQVBxQixDQWFyQjs7QUFDQSxRQUFNUyxVQUFVLEdBQUdQLDRDQUFLLENBQUNRLE1BQU4sQ0FBYSxJQUFiLENBQW5CO0FBQ0FDLDJFQUFlLENBQUNGLFVBQUQsRUFBYUosV0FBYixDQUFmO0FBRUEsc0JBQ0k7QUFBSyxPQUFHLEVBQUVJLFVBQVY7QUFBc0IsYUFBUyxFQUFFSDtBQUFqQyxLQUFpRFAsSUFBakQsR0FFS0csNENBQUssQ0FBQ1UsUUFBTixDQUFlQyxHQUFmLENBQW1CZixRQUFuQixFQUE4QmdCLEtBQUQsSUFBVztBQUNyQyxRQUFJLGVBQUNaLDRDQUFLLENBQUNhLGNBQU4sQ0FBcUJELEtBQXJCLENBQUwsRUFBa0M7QUFDOUIsYUFBT0EsS0FB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QyxnQkFBVCxDQUEwQixXQUExQixFQUF1Q04sa0JBQXZDO0FBQ0EsV0FBTyxNQUFNO0FBQ1Q7QUFDQUssY0FBUSxDQUFDRSxtQkFBVCxDQUE2QixXQUE3QixFQUEwQ1Asa0JBQTFDO0FBQ0gsS0FIRDtBQUlILEdBakJELEVBaUJHLENBQUNILEdBQUQsQ0FqQkg7QUFrQkg7QUFFRDs7Ozs7QUFJQSxTQUFTVyxXQUFULENBQXFCcEQsS0FBckIsRUFBNEI7QUFDeEIsUUFBTWEsVUFBVSxHQUFHQyxNQUFNLENBQUMsSUFBRCxDQUF6QjtBQUNBQyxpQkFBZSxDQUFDRixVQUFELENBQWY7QUFFQSxzQkFBTztBQUFLLE9BQUcsRUFBRUE7QUFBVixLQUF1QmIsS0FBSyxDQUFDRSxRQUE3QixDQUFQO0FBQ0gsQyIsImZpbGUiOiJOYXZNZW51VXNlcl9idW5kbGUuanMiLCJzb3VyY2VzQ29udGVudCI6WyJpbXBvcnQgUmVhY3QgZnJvbSAncmVhY3QnO1xuaW1wb3J0IGNsYXNzTmFtZXMgZnJvbSAnY2xhc3NuYW1lcyc7XG5pbXBvcnQgbWF0Y2hDb21wb25lbnQgZnJvbSAnLi4vbGliL21hdGNoLWNvbXBvbmVudC5qcyc7XG5pbXBvcnQgdXNlT3V0c2lkZUNsaWNrIGZyb20gJy4uL2xpYi91c2Utb3V0c2lkZS1jbGljay5qcyc7XG5cbmNvbnN0IGlzRHJvcGRvd25Ub2dnbGUgPSBtYXRjaENvbXBvbmVudChEcm9wZG93blRvZ2dsZSk7XG5jb25zdCBpc0Ryb3Bkb3duTWVudSA9IG1hdGNoQ29tcG9uZW50KERyb3Bkb3duTWVudSk7XG5cbmZ1bmN0aW9uIERyb3Bkb3duKHByb3BzKSB7XG4gICAgY29uc3QgeyBjbGFzc05hbWUsIGNoaWxkcmVuLCAuLi5yZXN0IH0gPSBwcm9wcztcblxuICAgIGNvbnN0IFtvcGVuLCBzZXRPcGVuXSA9IFJlYWN0LnVzZVN0YXRlKGZhbHNlKTtcbiAgICBjb25zdCBoYW5kbGVUb2dnbGUgPSAoKSA9PiBzZXRPcGVuKCFvcGVuKTtcbiAgICBjb25zdCBoYW5kbGVDbG9zZSA9ICgpID0+IHNldE9wZW4oZmFsc2UpO1xuXG4gICAgY29uc3QgY2xhc3NuYW1lcyA9IGNsYXNzTmFtZXMoe1xuICAgICAgICBjbGFzc05hbWUsXG4gICAgICAgIGRyb3Bkb3duOiB0cnVlLFxuICAgICAgICBvcGVuLFxuICAgIH0pO1xuXG4gICAgLy8gRXZlbnQgbGlzdGVuZXIgaXMgYWx3YXlzIGFjdGl2ZS4uLiBwcm9ibGVtP1xuICAgIGNvbnN0IHdyYXBwZXJSZWYgPSBSZWFjdC51c2VSZWYobnVsbCk7XG4gICAgdXNlT3V0c2lkZUNsaWNrKHdyYXBwZXJSZWYsIGhhbmRsZUNsb3NlKTtcblxuICAgIHJldHVybiAoXG4gICAgICAgIDxkaXYgcmVmPXt3cmFwcGVyUmVmfSBjbGFzc05hbWU9e2NsYXNzbmFtZXN9IHsuLi5yZXN0fT5cbiAgICAgICAgICAgIHsvKiBNYXAgY2hpbGRyZW4gJiBpbmplY3QgbGlzdGVuZXJzICovfVxuICAgICAgICAgICAge1JlYWN0LkNoaWxkcmVuLm1hcChjaGlsZHJlbiwgKGNoaWxkKSA9PiB7XG4gICAgICAgICAgICAgICAgaWYgKCFSZWFjdC5pc1ZhbGlkRWxlbWVudChjaGlsZCkpIHtcbiAgICAgICAgICAgICAgICAgICAgcmV0dXJuIGNoaWxkO1xuICAgICAgICAgICAgICAgIH1cblxuICAgICAgICAgICAgICAgIC8vIEJ1dHRvbiB0b2dnbGUgZHJvcGRvd24gbWVudVxuICAgICAgICAgICAgICAgIGlmIChpc0Ryb3Bkb3duVG9nZ2xlKGNoaWxkKSkge1xuICAgICAgICAgICAgICAgICAgICByZXR1cm4gUmVhY3QuY2xvbmVFbGVtZW50KGNoaWxkLCB7XG4gICAgICAgICAgICAgICAgICAgICAgICBoYW5kbGVDbGljazogaGFuZGxlVG9nZ2xlLFxuICAgICAgICAgICAgICAgICAgICB9KTtcbiAgICAgICAgICAgICAgICB9XG5cbiAgICAgICAgICAgICAgICByZXR1cm4gY2hpbGQ7XG4gICAgICAgICAgICB9KX1cbiAgICAgICAgPC9kaXY+XG4gICAgKTtcbn1cblxuZnVuY3Rpb24gRHJvcGRvd25Ub2dnbGUoeyBjbGFzc05hbWUsIGNoaWxkcmVuLCBoYW5kbGVDbGljayB9KSB7XG4gICAgcmV0dXJuIChcbiAgICAgICAgPGJ1dHRvbiBjbGFzc05hbWU9e2Bkcm9wZG93bi10b2dnbGUgJHtjbGFzc05hbWV9YH0gdHlwZT1cImJ1dHRvblwiIG9uQ2xpY2s9e2hhbmRsZUNsaWNrfSBhcmlhLWhhc3BvcHVwPVwidHJ1ZVwiIGFyaWEtZXhwYW5kZWQ9XCJ0cnVlXCI+XG4gICAgICAgICAgICB7Y2hpbGRyZW59XG4gICAgICAgIDwvYnV0dG9uPlxuICAgICk7XG59XG5cbmZ1bmN0aW9uIERyb3Bkb3duTWVudSh7IGNsYXNzTmFtZSwgY2hpbGRyZW4gfSkge1xuICAgIHJldHVybiAoXG4gICAgICAgIDxkaXYgY2xhc3NOYW1lPXtgZHJvcGRvd24tbWVudSBsaWdodCAke2NsYXNzTmFtZX1gfSByb2xlPVwibWVudVwiPlxuICAgICAgICAgICAge2NoaWxkcmVufVxuICAgICAgICA8L2Rpdj5cbiAgICApO1xufVxuXG5mdW5jdGlvbiBEcm9wZG93bkl0ZW0oeyBjaGlsZHJlbiB9KSB7XG4gICAgcmV0dXJuIChcbiAgICAgICAgPGRpdiBjbGFzc05hbWU9XCJkcm9wZG93bi1pdGVtXCIgcm9sZT1cIm1lbnVpdGVtXCI+e2NoaWxkcmVufTwvZGl2PlxuICAgICk7XG59XG5cbkRyb3Bkb3duLlRvZ2dsZSA9IERyb3Bkb3duVG9nZ2xlO1xuRHJvcGRvd24uTWVudSA9IERyb3Bkb3duTWVudTtcbkRyb3Bkb3duLkl0ZW0gPSBEcm9wZG93bkl0ZW07XG5cbmV4cG9ydCBkZWZhdWx0IERyb3Bkb3duO1xuIiwiaW1wb3J0IFJlYWN0IGZyb20gJ3JlYWN0JztcbmltcG9ydCBEcm9wZG93biBmcm9tICcuL0Ryb3Bkb3duLmpzeCc7XG5pbXBvcnQgVXNlciBmcm9tICcuL1VzZXIuanN4JztcblxuY29uc29sZS5sb2coJzxUb3BOYXZVc2VyPiBoYXMgYmVlbiBsYXp5IGxvYWRlZCEnKTtcblxuZXhwb3J0IGRlZmF1bHQgZnVuY3Rpb24gVG9wTmF2VXNlcih7IHVzZXJuYW1lIH0pIHtcbiAgICByZXR1cm4gKFxuICAgICAgICA8RHJvcGRvd24gaWQ9XCJsb2dpbi11c2VyLWRyb3Bkb3duXCI+XG4gICAgICAgICAgICA8RHJvcGRvd24uVG9nZ2xlIGNsYXNzTmFtZT1cImFjY2Vzcy1idXR0b25cIj5cbiAgICAgICAgICAgICAgICA8VXNlciB1c2VybmFtZT17dXNlcm5hbWV9IGhyZWY9XCJcIiBhdmF0YXI9XCJcIiAvPlxuICAgICAgICAgICAgPC9Ecm9wZG93bi5Ub2dnbGU+XG4gICAgICAgICAgICA8RHJvcGRvd24uTWVudT5cbiAgICAgICAgICAgICAgICA8RHJvcGRvd24uSXRlbT5mb288L0Ryb3Bkb3duLkl0ZW0+XG4gICAgICAgICAgICAgICAgPERyb3Bkb3duLkl0ZW0+PGEgaHJlZj17YC9+JHt1c2VybmFtZX1gfT5Qcm9maWxlPC9hPjwvRHJvcGRvd24uSXRlbT5cbiAgICAgICAgICAgICAgICA8RHJvcGRvd24uSXRlbT48YSBocmVmPXtgL34ke3VzZXJuYW1lfS9nYW1lc2B9PkdhbWVzPC9hPjwvRHJvcGRvd24uSXRlbT5cbiAgICAgICAgICAgICAgICA8RHJvcGRvd24uSXRlbT48YSBocmVmPVwiL2xvZ2luLnBocD9kbz1sb2dvdXRcIj5Mb2cgb3V0PC9hPjwvRHJvcGRvd24uSXRlbT5cbiAgICAgICAgICAgIDwvRHJvcGRvd24uTWVudT5cbiAgICAgICAgPC9Ecm9wZG93bj5cbiAgICApO1xufVxuIiwiaW1wb3J0IFJlYWN0IGZyb20gJ3JlYWN0JztcblxuZXhwb3J0IGRlZmF1bHQgZnVuY3Rpb24gVXNlcihwcm9wcykge1xuICAgIGxldCB7IHVzZXJuYW1lLCBhdmF0YXIsIGhyZWYgfSA9IHByb3BzO1xuXG4gICAgaWYgKGhyZWYgPT09IHVuZGVmaW5lZCkge1xuICAgICAgICBocmVmID0gYH4ke3VzZXJuYW1lfWA7XG4gICAgfVxuXG4gICAgbGV0IHRhZztcbiAgICBjb25zdCB0YWdDaGlsZCA9IDxzcGFuIGNsYXNzTmFtZT1cInVzZXItdXNlcm5hbWVcIj57dXNlcm5hbWV9PC9zcGFuPjtcbiAgICBpZiAoaHJlZikge1xuICAgICAgICB0YWcgPSBSZWFjdC5jcmVhdGVFbGVtZW50KCdhJywgeyBocmVmLCBjbGFzc05hbWU6ICd1c2VyLWxpbmsnIH0sIHRhZ0NoaWxkKTtcbiAgICB9IGVsc2Uge1xuICAgICAgICB0YWcgPSBSZWFjdC5jcmVhdGVFbGVtZW50KCdzcGFuJywgeyBjbGFzc05hbWU6ICd1c2VyLWxpbmsnIH0sIHRhZ0NoaWxkKTtcbiAgICB9XG5cbiAgICByZXR1cm4gKFxuICAgICAgICA8c3BhbiBjbGFzc05hbWU9XCJ1c2VyXCI+XG4gICAgICAgICAgICB7dGFnfVxuICAgICAgICA8L3NwYW4+XG4gICAgKTtcbn1cbiIsImNvbnN0IG1hdGNoQ29tcG9uZW50ID0gKENvbXBvbmVudCkgPT4gKGMpID0+IHtcclxuICAgIC8vIFJlYWN0IENvbXBvbmVudFxyXG4gICAgaWYgKGMudHlwZSA9PT0gQ29tcG9uZW50KSB7XHJcbiAgICAgICAgcmV0dXJuIHRydWU7XHJcbiAgICB9XHJcblxyXG4gICAgLy8gTWF0Y2hpbmcgY29tcG9uZW50VHlwZVxyXG4gICAgaWYgKGMucHJvcHMgJiYgYy5wcm9wcy5jb21wb25lbnRUeXBlID09PSBDb21wb25lbnQpIHtcclxuICAgICAgICByZXR1cm4gdHJ1ZTtcclxuICAgIH1cclxuXHJcbiAgICByZXR1cm4gZmFsc2U7XHJcbn07XHJcblxyXG5leHBvcnQgZGVmYXVsdCBtYXRjaENvbXBvbmVudDsiLCJpbXBvcnQgUmVhY3QgZnJvbSAncmVhY3QnO1xuXG4vKipcbiAqIEhvb2sgdGhhdCBhbGVydHMgY2xpY2tzIG91dHNpZGUgb2YgdGhlIHBhc3NlZCByZWZcbiAqL1xuZXhwb3J0IGRlZmF1bHQgZnVuY3Rpb24gdXNlT3V0c2lkZUNsaWNrKHJlZiwgY2FsbGJhY2spIHtcbiAgICBSZWFjdC51c2VFZmZlY3QoKCkgPT4ge1xuICAgICAgICAvKipcbiAgICAgICAgICogQWxlcnQgaWYgY2xpY2tlZCBvbiBvdXRzaWRlIG9mIGVsZW1lbnRcbiAgICAgICAgICovXG4gICAgICAgIGZ1bmN0aW9uIGhhbmRsZUNsaWNrT3V0c2lkZShldmVudCkge1xuICAgICAgICAgICAgaWYgKHJlZi5jdXJyZW50ICYmICFyZWYuY3VycmVudC5jb250YWlucyhldmVudC50YXJnZXQpKSB7XG4gICAgICAgICAgICAgICAgY29uc29sZS5sb2coJ291dHNpZGUgY2xpY2sgZGV0ZWN0ZWQnLCBjYWxsYmFjayk7XG4gICAgICAgICAgICAgICAgY2FsbGJhY2soKTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfVxuXG4gICAgICAgIC8vIEJpbmQgdGhlIGV2ZW50IGxpc3RlbmVyXG4gICAgICAgIGRvY3VtZW50LmFkZEV2ZW50TGlzdGVuZXIoJ21vdXNlZG93bicsIGhhbmRsZUNsaWNrT3V0c2lkZSk7XG4gICAgICAgIHJldHVybiAoKSA9PiB7XG4gICAgICAgICAgICAvLyBDbGVhbiB1cCBiZWZvcmUgY2FsbGluZyB0aGUgZWZmZWN0IGFnYWluIG9uIHRoZSBuZXh0IHJlbmRlclxuICAgICAgICAgICAgZG9jdW1lbnQucmVtb3ZlRXZlbnRMaXN0ZW5lcignbW91c2Vkb3duJywgaGFuZGxlQ2xpY2tPdXRzaWRlKTtcbiAgICAgICAgfTtcbiAgICB9LCBbcmVmXSk7XG59XG5cbi8qKlxuICogRXhhbXBsZSB1c2U6XG4gKiBDb21wb25lbnQgdGhhdCBhbGVydHMgaWYgeW91IGNsaWNrIG91dHNpZGUgb2YgaXRcbiAqL1xuZnVuY3Rpb24gTXlDb21wb25lbnQocHJvcHMpIHtcbiAgICBjb25zdCB3cmFwcGVyUmVmID0gdXNlUmVmKG51bGwpO1xuICAgIHVzZU91dHNpZGVDbGljayh3cmFwcGVyUmVmKTtcblxuICAgIHJldHVybiA8ZGl2IHJlZj17d3JhcHBlclJlZn0+e3Byb3BzLmNoaWxkcmVufTwvZGl2Pjtcbn1cbiJdLCJzb3VyY2VSb290IjoiIn0=