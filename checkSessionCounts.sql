SELECT sessions.id, sessions.name, sessions.filled, COUNT( students.session1 ) 
FROM sessions
INNER JOIN students ON sessions.id = students.session1
WHERE students.session1 = sessions.id
GROUP BY sessions.id
