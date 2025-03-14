import React, { useState } from 'react';
import { Box, TextField, Button, Typography } from '@mui/material';

const ContactForm = () => {
  // Form state
  const [formData, setFormData] = useState({
    firstName: '',
    lastName: '',
    email: '',
    contactNumber: '',
    question: '',
  });
  
  // Error messages
  const [errors, setErrors] = useState({});
  const [submissionMessage, setSubmissionMessage] = useState('');

  // Validate the form fields
  const validate = () => {
    let tempErrors = {};
    if (!formData.firstName.trim()) tempErrors.firstName = "First Name is required.";
    if (!formData.lastName.trim()) tempErrors.lastName = "Last Name is required.";
    if (!formData.email.trim()) tempErrors.email = "Email is required.";
    else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) tempErrors.email = "Please enter a valid email address.";
    setErrors(tempErrors);
    return Object.keys(tempErrors).length === 0;
  };

  // Handle form submission
  const handleSubmit = (e) => {
    e.preventDefault();
    if (validate()) {
      // Replace with your API call or submission logic
      setSubmissionMessage(`Thank you, ${formData.firstName}! Your message was received.`);
      // Optionally reset form fields:
      setFormData({
        firstName: '',
        lastName: '',
        email: '',
        contactNumber: '',
        question: '',
      });
    }
  };

  // Handle input changes
  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
  };

  return (
    <Box
      component="form"
      onSubmit={handleSubmit}
      sx={{
        display: 'flex',
        flexDirection: 'column',
        gap: 2,
        width: '100%',
        maxWidth: '400px',
        mx: 'auto'
      }}
    >
      <TextField
        label="First Name"
        name="firstName"
        value={formData.firstName}
        onChange={handleInputChange}
        error={Boolean(errors.firstName)}
        helperText={errors.firstName}
        required
      />
      <TextField
        label="Last Name"
        name="lastName"
        value={formData.lastName}
        onChange={handleInputChange}
        error={Boolean(errors.lastName)}
        helperText={errors.lastName}
        required
      />
      <TextField
        label="Email"
        name="email"
        type="email"
        value={formData.email}
        onChange={handleInputChange}
        error={Boolean(errors.email)}
        helperText={errors.email}
        required
      />
      <TextField
        label="Contact Number"
        name="contactNumber"
        value={formData.contactNumber}
        onChange={handleInputChange}
      />
      <TextField
        label="Question"
        name="question"
        multiline
        rows={4}
        value={formData.question}
        onChange={handleInputChange}
        helperText="Ask us anything! (Max 300 characters)"
        inputProps={{ maxLength: 300 }}
      />
      <Button variant="contained" type="submit">Submit</Button>
      {submissionMessage && (
        <Typography variant="body2" color="success.main">
          {submissionMessage}
        </Typography>
      )}
    </Box>
  );
};

export default ContactForm;

