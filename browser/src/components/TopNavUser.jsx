import React from 'react';
import Dropdown from './ui/Dropdown.jsx';
import User from './User.jsx';

console.log('<TopNavUser> has been lazy loaded!');

export default function TopNavUser({ username }) {
    return (
        <Dropdown id="login-user-dropdown">
            <Dropdown.Toggle classes={{ 'button-header': true }}>
                <User username={username} href="" avatar="" />
            </Dropdown.Toggle>
            <Dropdown.Menu>
                <Dropdown.Item><a href={`/~${username}`}>Profile</a></Dropdown.Item>
                <Dropdown.Item><a href={`/~${username}/games`}>Games</a></Dropdown.Item>
                <Dropdown.Item><a href="/login.php?do=logout">Log out</a></Dropdown.Item>
            </Dropdown.Menu>
        </Dropdown>
    );
}
