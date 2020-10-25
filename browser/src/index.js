// Entry point for React components on all pages

import React from 'react';
import ReactDOM from 'react-dom';

// Stylesheets that get injected into <head>
import 'normalize.css';
import '../styles/app.scss';

// Components to render
import Colophon from './components/layout/Colophon.jsx';
import Header from './components/layout/Header.jsx';

// Grab data-* properties from <header> element and pass them as props to <Header> component
const headerElement = document.getElementById('header');
ReactDOM.render(React.createElement(Header, {...headerElement.dataset}), headerElement);

ReactDOM.render(
    React.createElement(Colophon),
    document.getElementById('colophon'),
);

// Router

// const element = (
//     <>
//         <Router>
//             <Page />
//         </Router>
//     </>
// );

// ReactDOM.render(element, document.getElementById('root'));
