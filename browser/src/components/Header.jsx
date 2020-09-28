import React from 'react';
import NavMenu from './NavMenu.jsx';
import Login from './Login.jsx';
import Search from './Search.jsx';

export default function Header(props) {
    const { username } = props;

    return (
        <>
            <NavMenu />
            <Login username={username} />
            <Search />
        </>
    );
}
