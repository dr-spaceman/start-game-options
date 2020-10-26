import React from 'react';
import Dropdown from '../ui/Dropdown.jsx';
import User from '../User.jsx';
import Button from '../ui/Button.jsx';

console.log('<TopNavUser> has been lazy loaded!');

function logout() {
    fetch('/logout.php').then((response) => {
        if (response.ok) {
            window.location.reload();
        }
    });
}

export default function TopNavUser({ username }) {
    return (
        <Dropdown id="login-user-dropdown">
            <Dropdown.Toggle classes={{ 'button-header': true }}>
                <User username={username} href="" avatar="" />
            </Dropdown.Toggle>
            <Dropdown.Menu>
                <Dropdown.Item><a href={`/~${username}`}>Profile</a></Dropdown.Item>
                <Dropdown.Item><a href={`/~${username}/games`}>Games</a></Dropdown.Item>
                <Dropdown.Item>
                    <Button variant="link" onClick={logout}>Log out</Button>
                </Dropdown.Item>
            </Dropdown.Menu>
        </Dropdown>
    );
}
