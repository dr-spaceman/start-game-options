import React from 'react';

export default function User(props) {
    let { username, avatar, href } = props;

    if (href === undefined) {
        href = `~${username}`;
    }

    let tag;
    const tagChild = <span className="user-username">{username}</span>;
    if (href) {
        tag = React.createElement('a', { href, className: 'user-link' }, tagChild);
    } else {
        tag = React.createElement('span', { className: 'user-link' }, tagChild);
    }

    return (
        <span className="user">
            {tag}
        </span>
    );
}
