import React from 'react';
import Dropdown from './Dropdown.jsx';
import User from './User.jsx';

console.log('<TopNavUser> has been lazy loaded!');

export default function TopNavUser({ username }) {
    return (
        <Dropdown id="login-user-dropdown">
            <Dropdown.Toggle className="access-button">
                <User username={username} href="" avatar="" />
            </Dropdown.Toggle>
            <Dropdown.Menu>
                <Dropdown.Item>foo</Dropdown.Item>
                <Dropdown.Item><a href={`/~${username}`}>Profile</a></Dropdown.Item>
                <Dropdown.Item><a href={`/~${username}/games`}>Games</a></Dropdown.Item>
                <Dropdown.Item><a href="/login.php?do=logout">Log out</a></Dropdown.Item>
            </Dropdown.Menu>
        </Dropdown>
    );
}
