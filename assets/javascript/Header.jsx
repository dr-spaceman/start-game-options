import React from 'react';
import GiHamburgerMenu from 'react-icons/gi';
import Login from './Login.jsx';
import Search from './Search.jsx';

export default function Header(props) {
    const { username } = props;

    return (
        <>
            <h2><a href="/" title="Videogam.in">Videogam.in, a site about videogames</a></h2>
            <Login username={username} />
            <Search />
        </>
    );
}
